<?php

namespace App\Http\Controllers\Landlord;

use App\Enums\LeaseStatus;
use App\Enums\OrganizationRole;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Concerns\ResolvesOrganization;
use App\Http\Controllers\Controller;
use App\Http\Requests\Landlord\StoreTenantRequest;
use App\Http\Requests\Landlord\UpdateTenantRequest;
use App\Models\Document;
use App\Models\Lease;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class TenantController extends Controller
{
    use ResolvesOrganization;

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Tenant::class);

        $orgId = $this->organizationId();
        $search = trim((string) $request->string('search'));

        $tenants = Tenant::query()
            ->forOrganization($orgId)
            ->with([
                'leases' => fn ($q) => $q
                    ->whereIn('status', [LeaseStatus::Active, LeaseStatus::Expiring, LeaseStatus::Draft])
                    ->with(['property:id,name', 'unit:id,name'])
                    ->latest('start_date'),
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString()
            ->through(function (Tenant $tenant) {
                $lease = $tenant->leases->first();
                $latestPayment = $tenant->rentPayments()
                    ->latest('due_date')
                    ->first();

                return [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'email' => $tenant->email,
                    'phone' => $tenant->phone,
                    'has_account' => $tenant->user_id !== null,
                    'property' => $lease?->property?->name,
                    'unit' => $lease?->unit?->name,
                    'lease_status' => $lease?->status?->value,
                    'lease_status_label' => $lease?->status?->label(),
                    'lease_status_color' => $lease?->status?->color(),
                    'monthly_rent' => $lease ? (float) $lease->monthly_rent : null,
                    'payment_status' => $latestPayment?->status?->value,
                    'payment_status_label' => $latestPayment?->status?->label(),
                    'payment_status_color' => $latestPayment?->status?->color(),
                ];
            });

        return Inertia::render('Landlord/Tenants/Index', [
            'tenants' => $tenants,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Tenant::class);

        return Inertia::render('Landlord/Tenants/Create');
    }

    public function store(StoreTenantRequest $request): RedirectResponse
    {
        $this->authorize('create', Tenant::class);

        $orgId = $this->organizationId();

        $tenant = DB::transaction(function () use ($request, $orgId) {
            $userId = null;

            if ($request->boolean('create_account')) {
                $email = $request->string('email')->toString();

                if ($email === '') {
                    abort(422, 'Email is required to create a login account.');
                }

                $user = User::query()->firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $request->string('name')->toString(),
                        'password' => Hash::make($request->string('password')->toString()),
                        'phone' => $request->input('phone'),
                        'email_verified_at' => now(),
                        'current_organization_id' => $orgId,
                    ]
                );

                if (! $user->wasRecentlyCreated && $request->filled('password')) {
                    $user->forceFill([
                        'password' => Hash::make($request->string('password')->toString()),
                    ])->save();
                }

                if (! $user->organizations()->where('organizations.id', $orgId)->exists()) {
                    $user->organizations()->attach($orgId, [
                        'role' => OrganizationRole::Tenant->value,
                    ]);
                }

                $user->forceFill(['current_organization_id' => $orgId])->save();

                $userId = $user->id;
            }

            return Tenant::query()->create([
                'organization_id' => $orgId,
                'user_id' => $userId,
                'name' => $request->string('name')->toString(),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'notes' => $request->input('notes'),
                'emergency_contact_name' => $request->input('emergency_contact_name'),
                'emergency_contact_phone' => $request->input('emergency_contact_phone'),
            ]);
        });

        return redirect()
            ->route('landlord.tenants.show', $tenant)
            ->with('success', 'Tenant created successfully.');
    }

    public function show(Tenant $tenant): Response
    {
        $this->authorize('view', $tenant);

        $tenant->load([
            'user:id,name,email',
            'leases' => fn ($q) => $q->with(['property:id,name', 'unit:id,name'])->latest('start_date'),
            'rentPayments' => fn ($q) => $q->latest('due_date')->limit(20),
            'maintenanceRequests' => fn ($q) => $q->with('property:id,name')->latest()->limit(10),
        ]);

        $leaseIds = $tenant->leases->pluck('id');
        $propertyIds = $tenant->leases->pluck('property_id');

        $documents = Document::query()
            ->forOrganization($this->organizationId())
            ->where(function ($query) use ($tenant, $leaseIds, $propertyIds) {
                $query->where(function ($q) use ($tenant) {
                    $q->where('documentable_type', Tenant::class)
                        ->where('documentable_id', $tenant->id);
                })->orWhere(function ($q) use ($leaseIds) {
                    $q->where('documentable_type', Lease::class)
                        ->whereIn('documentable_id', $leaseIds);
                })->orWhere(function ($q) use ($propertyIds) {
                    $q->where('documentable_type', \App\Models\Property::class)
                        ->whereIn('documentable_id', $propertyIds);
                });
            })
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (Document $doc) => [
                'id' => $doc->id,
                'title' => $doc->title,
                'type' => $doc->type?->value,
                'type_label' => $doc->type?->label(),
                'created_at' => $doc->created_at?->toDateString(),
            ]);

        $activeLease = $tenant->leases->first(
            fn (Lease $lease) => in_array($lease->status, [LeaseStatus::Active, LeaseStatus::Expiring], true)
        ) ?? $tenant->leases->first();

        return Inertia::render('Landlord/Tenants/Show', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'email' => $tenant->email,
                'phone' => $tenant->phone,
                'notes' => $tenant->notes,
                'emergency_contact_name' => $tenant->emergency_contact_name,
                'emergency_contact_phone' => $tenant->emergency_contact_phone,
                'has_account' => $tenant->user_id !== null,
                'user' => $tenant->user ? [
                    'name' => $tenant->user->name,
                    'email' => $tenant->user->email,
                ] : null,
                'lease' => $activeLease ? [
                    'id' => $activeLease->id,
                    'property' => $activeLease->property?->name,
                    'unit' => $activeLease->unit?->name,
                    'monthly_rent' => (float) $activeLease->monthly_rent,
                    'start_date' => $activeLease->start_date?->toDateString(),
                    'end_date' => $activeLease->end_date?->toDateString(),
                    'status' => $activeLease->status?->value,
                    'status_label' => $activeLease->status?->label(),
                    'status_color' => $activeLease->status?->color(),
                ] : null,
                'payments' => $tenant->rentPayments->map(fn ($payment) => [
                    'id' => $payment->id,
                    'amount' => (float) $payment->amount,
                    'amount_paid' => (float) $payment->amount_paid,
                    'due_date' => $payment->due_date?->toDateString(),
                    'payment_date' => $payment->payment_date?->toDateString(),
                    'period_label' => $payment->period_label,
                    'status' => $payment->status?->value,
                    'status_label' => $payment->status?->label(),
                    'status_color' => $payment->status?->color(),
                ]),
                'maintenance' => $tenant->maintenanceRequests->map(fn ($item) => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'property' => $item->property?->name,
                    'status' => $item->status?->value,
                    'status_label' => $item->status?->label(),
                    'status_color' => $item->status?->color(),
                    'priority' => $item->priority?->value,
                    'priority_label' => $item->priority?->label(),
                    'priority_color' => $item->priority?->color(),
                    'created_at' => $item->created_at?->toDateString(),
                ]),
                'documents' => $documents,
            ],
        ]);
    }

    public function edit(Tenant $tenant): Response
    {
        $this->authorize('update', $tenant);

        return Inertia::render('Landlord/Tenants/Edit', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'email' => $tenant->email,
                'phone' => $tenant->phone,
                'notes' => $tenant->notes,
                'emergency_contact_name' => $tenant->emergency_contact_name,
                'emergency_contact_phone' => $tenant->emergency_contact_phone,
            ],
        ]);
    }

    public function update(UpdateTenantRequest $request, Tenant $tenant): RedirectResponse
    {
        $this->authorize('update', $tenant);

        $tenant->update($request->validated());

        return redirect()
            ->route('landlord.tenants.show', $tenant)
            ->with('success', 'Tenant updated successfully.');
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        $this->authorize('delete', $tenant);

        $hasActiveLease = $tenant->leases()
            ->whereIn('status', [LeaseStatus::Active, LeaseStatus::Expiring])
            ->exists();

        if ($hasActiveLease) {
            return back()->with('error', 'Cannot delete a tenant with an active lease.');
        }

        $tenant->delete();

        return redirect()
            ->route('landlord.tenants.index')
            ->with('success', 'Tenant deleted.');
    }
}
