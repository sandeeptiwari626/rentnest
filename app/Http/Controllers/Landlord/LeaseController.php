<?php

namespace App\Http\Controllers\Landlord;

use App\Enums\LeaseStatus;
use App\Enums\PropertyStatus;
use App\Http\Controllers\Concerns\ResolvesOrganization;
use App\Http\Controllers\Controller;
use App\Http\Requests\Landlord\StoreLeaseRequest;
use App\Http\Requests\Landlord\UpdateLeaseRequest;
use App\Models\Lease;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class LeaseController extends Controller
{
    use ResolvesOrganization;

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Lease::class);

        $orgId = $this->organizationId();

        $leases = Lease::query()
            ->forOrganization($orgId)
            ->with(['property:id,name', 'unit:id,name', 'tenant:id,name'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Lease $lease) => [
                'id' => $lease->id,
                'property' => $lease->property?->name,
                'unit' => $lease->unit?->name,
                'tenant' => $lease->tenant?->name,
                'monthly_rent' => (float) $lease->monthly_rent,
                'start_date' => $lease->start_date?->toDateString(),
                'end_date' => $lease->end_date?->toDateString(),
                'status' => $lease->status?->value,
                'status_label' => $lease->status?->label(),
                'status_color' => $lease->status?->color(),
                'expiring_soon' => $lease->end_date
                    && $lease->end_date->lte(now()->addDays(60))
                    && in_array($lease->status, [LeaseStatus::Active, LeaseStatus::Expiring], true),
            ]);

        return Inertia::render('Landlord/Leases/Index', [
            'leases' => $leases,
            'filters' => ['status' => $request->string('status')->toString()],
            'statusOptions' => $this->enumOptions(LeaseStatus::class),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Lease::class);
        $orgId = $this->organizationId();

        return Inertia::render('Landlord/Leases/Create', [
            'properties' => Property::query()->forOrganization($orgId)->orderBy('name')->get(['id', 'name']),
            'units' => Unit::query()->forOrganization($orgId)->orderBy('name')->get(['id', 'property_id', 'name', 'rent_amount', 'status']),
            'tenants' => Tenant::query()->forOrganization($orgId)->orderBy('name')->get(['id', 'name', 'email']),
            'statusOptions' => $this->enumOptions(LeaseStatus::class),
        ]);
    }

    public function store(StoreLeaseRequest $request): RedirectResponse
    {
        $this->authorize('create', Lease::class);

        $orgId = $this->organizationId();
        $data = $request->safe()->except(['lease_document']);

        $lease = DB::transaction(function () use ($request, $orgId, $data) {
            $path = null;
            if ($request->hasFile('lease_document')) {
                $path = $request->file('lease_document')->store('leases', 'local');
            }

            $lease = Lease::query()->create([
                ...$data,
                'organization_id' => $orgId,
                'security_deposit' => $data['security_deposit'] ?? 0,
                'notice_period_days' => $data['notice_period_days'] ?? 30,
                'status' => $data['status'] ?? LeaseStatus::Draft->value,
                'lease_document_path' => $path,
            ]);

            if ($lease->status === LeaseStatus::Active) {
                $this->markOccupied($lease);
            }

            return $lease;
        });

        return redirect()
            ->route('landlord.leases.show', $lease)
            ->with('success', 'Lease created successfully.');
    }

    public function show(Lease $lease): Response
    {
        $this->authorize('view', $lease);

        $lease->load([
            'property:id,name,address,city',
            'unit:id,name',
            'tenant:id,name,email,phone',
            'rentPayments' => fn ($q) => $q->orderByDesc('due_date'),
        ]);

        $expiringSoon = $lease->end_date
            && $lease->end_date->lte(now()->addDays(60))
            && in_array($lease->status, [LeaseStatus::Active, LeaseStatus::Expiring], true);

        $timeline = collect([
            [
                'title' => 'Lease created',
                'description' => 'Lease record added to RentNest',
                'date' => $lease->created_at?->format('d M Y'),
                'status' => 'info',
            ],
            [
                'title' => 'Lease start',
                'description' => 'Tenancy begins',
                'date' => $lease->start_date?->format('d M Y'),
                'status' => 'success',
            ],
        ]);

        if ($lease->status === LeaseStatus::Active || $lease->status === LeaseStatus::Expiring) {
            $timeline->push([
                'title' => 'Lease activated',
                'description' => 'Property marked occupied',
                'date' => $lease->updated_at?->format('d M Y'),
                'status' => 'success',
            ]);
        }

        foreach ($lease->rentPayments->take(8) as $payment) {
            $timeline->push([
                'title' => ($payment->period_label ?: 'Rent').' · '.($payment->status?->label() ?? ''),
                'description' => '₹'.number_format((float) $payment->amount, 0),
                'date' => optional($payment->payment_date ?? $payment->due_date)->format('d M Y'),
                'status' => match ($payment->status?->value) {
                    'paid' => 'success',
                    'late' => 'danger',
                    'partial' => 'warning',
                    default => 'pending',
                },
            ]);
        }

        if ($lease->end_date) {
            $timeline->push([
                'title' => 'Lease end',
                'description' => $expiringSoon ? 'Expiring within 60 days' : 'Scheduled end date',
                'date' => $lease->end_date->format('d M Y'),
                'status' => $expiringSoon ? 'warning' : 'default',
            ]);
        }

        return Inertia::render('Landlord/Leases/Show', [
            'lease' => [
                'id' => $lease->id,
                'property' => [
                    'id' => $lease->property?->id,
                    'name' => $lease->property?->name,
                    'address' => $lease->property?->address,
                    'city' => $lease->property?->city,
                ],
                'unit' => $lease->unit?->name,
                'tenant' => [
                    'id' => $lease->tenant?->id,
                    'name' => $lease->tenant?->name,
                    'email' => $lease->tenant?->email,
                    'phone' => $lease->tenant?->phone,
                ],
                'start_date' => $lease->start_date?->toDateString(),
                'end_date' => $lease->end_date?->toDateString(),
                'monthly_rent' => (float) $lease->monthly_rent,
                'security_deposit' => (float) $lease->security_deposit,
                'rent_due_day' => $lease->rent_due_day,
                'notice_period_days' => $lease->notice_period_days,
                'status' => $lease->status?->value,
                'status_label' => $lease->status?->label(),
                'status_color' => $lease->status?->color(),
                'notes' => $lease->notes,
                'has_document' => filled($lease->lease_document_path),
                'payments' => $lease->rentPayments->map(fn ($payment) => [
                    'id' => $payment->id,
                    'amount' => (float) $payment->amount,
                    'amount_paid' => (float) $payment->amount_paid,
                    'due_date' => $payment->due_date?->toDateString(),
                    'status' => $payment->status?->value,
                    'status_label' => $payment->status?->label(),
                    'status_color' => $payment->status?->color(),
                    'period_label' => $payment->period_label,
                ]),
            ],
            'expiringSoon' => $expiringSoon,
            'timeline' => $timeline->values(),
        ]);
    }

    public function edit(Lease $lease): Response
    {
        $this->authorize('update', $lease);
        $orgId = $this->organizationId();

        return Inertia::render('Landlord/Leases/Edit', [
            'lease' => [
                'id' => $lease->id,
                'property_id' => $lease->property_id,
                'unit_id' => $lease->unit_id,
                'tenant_id' => $lease->tenant_id,
                'start_date' => $lease->start_date?->toDateString(),
                'end_date' => $lease->end_date?->toDateString(),
                'monthly_rent' => (float) $lease->monthly_rent,
                'security_deposit' => (float) $lease->security_deposit,
                'rent_due_day' => $lease->rent_due_day,
                'notice_period_days' => $lease->notice_period_days,
                'status' => $lease->status?->value,
                'notes' => $lease->notes,
            ],
            'properties' => Property::query()->forOrganization($orgId)->orderBy('name')->get(['id', 'name']),
            'units' => Unit::query()->forOrganization($orgId)->orderBy('name')->get(['id', 'property_id', 'name', 'rent_amount', 'status']),
            'tenants' => Tenant::query()->forOrganization($orgId)->orderBy('name')->get(['id', 'name', 'email']),
            'statusOptions' => $this->enumOptions(LeaseStatus::class),
        ]);
    }

    public function update(UpdateLeaseRequest $request, Lease $lease): RedirectResponse
    {
        $this->authorize('update', $lease);

        $data = $request->safe()->except(['lease_document']);
        $wasActive = $lease->status === LeaseStatus::Active;

        if ($request->hasFile('lease_document')) {
            if ($lease->lease_document_path) {
                Storage::disk('local')->delete($lease->lease_document_path);
            }
            $data['lease_document_path'] = $request->file('lease_document')->store('leases', 'local');
        }

        $lease->update($data);

        if (! $wasActive && $lease->fresh()->status === LeaseStatus::Active) {
            $this->markOccupied($lease);
        }

        return redirect()
            ->route('landlord.leases.show', $lease)
            ->with('success', 'Lease updated successfully.');
    }

    public function activate(Lease $lease): RedirectResponse
    {
        $this->authorize('update', $lease);

        DB::transaction(function () use ($lease) {
            $lease->update(['status' => LeaseStatus::Active]);
            $this->markOccupied($lease);
        });

        return back()->with('success', 'Lease activated. Property marked as occupied.');
    }

    public function terminate(Lease $lease): RedirectResponse
    {
        $this->authorize('update', $lease);

        if (! in_array($lease->status, [LeaseStatus::Active, LeaseStatus::Expiring, LeaseStatus::Draft], true)) {
            return back()->with('error', 'Only draft, active, or expiring leases can be terminated.');
        }

        DB::transaction(function () use ($lease) {
            $lease->update([
                'status' => LeaseStatus::Terminated,
                'end_date' => $lease->end_date && $lease->end_date->lte(now())
                    ? $lease->end_date
                    : now()->toDateString(),
            ]);
            $this->markVacantIfNoActiveLease($lease);
        });

        return back()->with('success', 'Lease terminated. You can delete it now if needed.');
    }

    public function expire(Lease $lease): RedirectResponse
    {
        $this->authorize('update', $lease);

        if (! in_array($lease->status, [LeaseStatus::Active, LeaseStatus::Expiring], true)) {
            return back()->with('error', 'Only active or expiring leases can be marked expired.');
        }

        DB::transaction(function () use ($lease) {
            $lease->update([
                'status' => LeaseStatus::Expired,
                'end_date' => $lease->end_date && $lease->end_date->lte(now())
                    ? $lease->end_date
                    : now()->toDateString(),
            ]);
            $this->markVacantIfNoActiveLease($lease);
        });

        return back()->with('success', 'Lease marked as expired. You can delete it now if needed.');
    }

    public function destroy(Lease $lease): RedirectResponse
    {
        $this->authorize('delete', $lease);

        if (in_array($lease->status, [LeaseStatus::Active, LeaseStatus::Expiring], true)) {
            return back()->with('error', 'Terminate or expire the lease before deleting.');
        }

        DB::transaction(function () use ($lease) {
            $this->markVacantIfNoActiveLease($lease);
            $lease->delete();
        });

        return redirect()
            ->route('landlord.leases.index')
            ->with('success', 'Lease deleted.');
    }

    protected function markOccupied(Lease $lease): void
    {
        $lease->property?->update(['status' => PropertyStatus::Occupied]);
        $lease->unit?->update(['status' => PropertyStatus::Occupied]);
    }

    protected function markVacantIfNoActiveLease(Lease $lease): void
    {
        $hasActiveLease = Lease::query()
            ->where('unit_id', $lease->unit_id)
            ->where('id', '!=', $lease->id)
            ->whereIn('status', [LeaseStatus::Active, LeaseStatus::Expiring])
            ->exists();

        if ($hasActiveLease) {
            return;
        }

        $lease->unit?->update(['status' => PropertyStatus::Vacant]);

        $propertyHasActive = Lease::query()
            ->where('property_id', $lease->property_id)
            ->where('id', '!=', $lease->id)
            ->whereIn('status', [LeaseStatus::Active, LeaseStatus::Expiring])
            ->exists();

        if (! $propertyHasActive) {
            $lease->property?->update(['status' => PropertyStatus::Vacant]);
        }
    }

    /**
     * @param  class-string<\BackedEnum>  $enum
     * @return array<int, array{value: string, label: string}>
     */
    protected function enumOptions(string $enum): array
    {
        return collect($enum::cases())->map(fn ($case) => [
            'value' => $case->value,
            'label' => method_exists($case, 'label') ? $case->label() : $case->name,
        ])->values()->all();
    }
}
