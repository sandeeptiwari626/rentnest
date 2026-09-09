<?php

namespace App\Http\Controllers\Landlord;

use App\Enums\LeaseStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Concerns\ResolvesOrganization;
use App\Http\Controllers\Controller;
use App\Http\Requests\Landlord\BulkDestroyPaymentsRequest;
use App\Http\Requests\Landlord\StorePaymentRequest;
use App\Http\Requests\Landlord\UpdatePaymentStatusRequest;
use App\Models\Lease;
use App\Models\RentPayment;
use App\Notifications\RentReceivedNotification;
use App\Support\PrivateUpload;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    use ResolvesOrganization;

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', RentPayment::class);

        $orgId = $this->organizationId();

        $payments = RentPayment::query()
            ->forOrganization($orgId)
            ->with(['tenant:id,name', 'property:id,name', 'lease:id'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->string('search'));
                $query->where(function ($q) use ($search) {
                    $q->where('period_label', 'like', "%{$search}%")
                        ->orWhere('reference_number', 'like', "%{$search}%")
                        ->orWhere('receipt_number', 'like', "%{$search}%")
                        ->orWhereHas('tenant', fn ($tq) => $tq->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('due_date')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (RentPayment $payment) => [
                'id' => $payment->id,
                'tenant' => $payment->tenant?->name,
                'property' => $payment->property?->name,
                'amount' => (float) $payment->amount,
                'amount_paid' => (float) $payment->amount_paid,
                'due_date' => $payment->due_date?->toDateString(),
                'payment_date' => $payment->payment_date?->toDateString(),
                'period_label' => $payment->period_label,
                'status' => $payment->status?->value,
                'status_label' => $payment->status?->label(),
                'status_color' => $payment->status?->color(),
                'receipt_number' => $payment->receipt_number,
            ]);

        return Inertia::render('Landlord/Payments/Index', [
            'payments' => $payments,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'status' => $request->string('status')->toString(),
            ],
            'statusOptions' => $this->enumOptions(PaymentStatus::class),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', RentPayment::class);
        $orgId = $this->organizationId();

        $leases = Lease::query()
            ->forOrganization($orgId)
            ->whereIn('status', [LeaseStatus::Active, LeaseStatus::Expiring, LeaseStatus::Draft])
            ->with(['tenant:id,name', 'property:id,name', 'unit:id,name'])
            ->latest()
            ->get()
            ->map(fn (Lease $lease) => [
                'id' => $lease->id,
                'label' => ($lease->property?->name ?? 'Property')
                    .' · '.($lease->unit?->name ?? 'Unit')
                    .' · '.($lease->tenant?->name ?? 'Tenant'),
                'monthly_rent' => (float) $lease->monthly_rent,
                'tenant_id' => $lease->tenant_id,
                'property_id' => $lease->property_id,
            ]);

        return Inertia::render('Landlord/Payments/Create', [
            'leases' => $leases,
            'statusOptions' => $this->enumOptions(PaymentStatus::class),
            'methodOptions' => $this->enumOptions(PaymentMethod::class),
        ]);
    }

    public function store(StorePaymentRequest $request): RedirectResponse
    {
        $this->authorize('create', RentPayment::class);

        $lease = Lease::query()
            ->forOrganization($this->organizationId())
            ->findOrFail($request->integer('lease_id'));

        $status = PaymentStatus::tryFrom((string) $request->input('status', PaymentStatus::Pending->value))
            ?? PaymentStatus::Pending;

        $amountPaid = (float) ($request->input('amount_paid') ?? (
            in_array($status, [PaymentStatus::Paid], true) ? $request->input('amount') : 0
        ));

        $proofPath = null;
        if ($request->hasFile('proof')) {
            $proofPath = PrivateUpload::store(
                $request->file('proof'),
                'payments/'.$this->organizationId(),
                'proof'
            );
        }

        $payment = RentPayment::query()->create([
            'organization_id' => $this->organizationId(),
            'lease_id' => $lease->id,
            'property_id' => $lease->property_id,
            'tenant_id' => $lease->tenant_id,
            'amount' => $request->input('amount'),
            'amount_paid' => $amountPaid,
            'due_date' => $request->input('due_date'),
            'payment_date' => $request->input('payment_date') ?? ($status === PaymentStatus::Paid ? now()->toDateString() : null),
            'payment_method' => $request->input('payment_method'),
            'reference_number' => $request->input('reference_number'),
            'status' => $status,
            'period_label' => $request->input('period_label'),
            'notes' => $request->input('notes'),
            'proof_path' => $proofPath,
            'receipt_number' => $status === PaymentStatus::Paid ? $this->nextReceiptNumber() : null,
        ]);

        if ($status === PaymentStatus::Paid) {
            $payment->loadMissing('tenant.user', 'property:id,name');
            $tenantUser = $payment->tenant?->user;

            if ($tenantUser) {
                $tenantUser->notify(new RentReceivedNotification($payment));
            }
        }

        return redirect()
            ->route('landlord.payments.show', $payment)
            ->with('success', 'Payment recorded successfully.');
    }

    public function show(RentPayment $payment): Response
    {
        $this->authorize('view', $payment);

        $payment->load(['tenant:id,name,email,phone', 'property:id,name,address,city', 'lease:id,start_date,end_date,monthly_rent']);

        return Inertia::render('Landlord/Payments/Show', [
            'payment' => [
                'id' => $payment->id,
                'amount' => (float) $payment->amount,
                'amount_paid' => (float) $payment->amount_paid,
                'due_date' => $payment->due_date?->toDateString(),
                'payment_date' => $payment->payment_date?->toDateString(),
                'payment_method' => $payment->payment_method?->value,
                'payment_method_label' => $payment->payment_method?->label(),
                'reference_number' => $payment->reference_number,
                'status' => $payment->status?->value,
                'status_label' => $payment->status?->label(),
                'status_color' => $payment->status?->color(),
                'period_label' => $payment->period_label,
                'notes' => $payment->notes,
                'proof_url' => $payment->proof_path
                    ? route('landlord.payments.proof', $payment)
                    : null,
                'proof_is_image' => $this->proofIsImage($payment->proof_path),
                'receipt_number' => $payment->receipt_number,
                'tenant' => $payment->tenant,
                'property' => $payment->property,
                'lease' => $payment->lease ? [
                    'id' => $payment->lease->id,
                    'monthly_rent' => (float) $payment->lease->monthly_rent,
                ] : null,
            ],
            'statusOptions' => $this->enumOptions(PaymentStatus::class),
            'methodOptions' => $this->enumOptions(PaymentMethod::class),
        ]);
    }

    public function edit(RentPayment $payment): Response
    {
        $this->authorize('update', $payment);

        $payment->load(['tenant:id,name', 'property:id,name']);

        return Inertia::render('Landlord/Payments/Edit', [
            'payment' => [
                'id' => $payment->id,
                'lease_id' => $payment->lease_id,
                'lease_label' => collect([
                    $payment->property?->name,
                    $payment->tenant?->name,
                ])->filter()->implode(' · '),
                'amount' => (float) $payment->amount,
                'amount_paid' => (float) $payment->amount_paid,
                'due_date' => $payment->due_date?->toDateString(),
                'payment_date' => $payment->payment_date?->toDateString(),
                'payment_method' => $payment->payment_method?->value,
                'reference_number' => $payment->reference_number,
                'status' => $payment->status?->value,
                'period_label' => $payment->period_label,
                'notes' => $payment->notes,
                'proof_url' => $payment->proof_path
                    ? route('landlord.payments.proof', $payment)
                    : null,
                'proof_is_image' => $this->proofIsImage($payment->proof_path),
            ],
            'statusOptions' => $this->enumOptions(PaymentStatus::class),
            'methodOptions' => $this->enumOptions(PaymentMethod::class),
        ]);
    }

    public function update(UpdatePaymentStatusRequest $request, RentPayment $payment): RedirectResponse
    {
        $this->authorize('update', $payment);

        $wasPaid = $payment->status === PaymentStatus::Paid;
        $status = PaymentStatus::from($request->string('status')->toString());
        $data = $request->safe()->except(['proof', 'remove_proof']);

        if ($status === PaymentStatus::Paid) {
            $data['amount_paid'] = $data['amount_paid'] ?? $payment->amount;
            $data['payment_date'] = $data['payment_date'] ?? now()->toDateString();
            $data['receipt_number'] = $payment->receipt_number ?: $this->nextReceiptNumber();
        }

        if ($request->boolean('remove_proof') && $payment->proof_path) {
            Storage::disk('local')->delete($payment->proof_path);
            $data['proof_path'] = null;
        }

        if ($request->hasFile('proof')) {
            if ($payment->proof_path) {
                Storage::disk('local')->delete($payment->proof_path);
            }

            $data['proof_path'] = PrivateUpload::store(
                $request->file('proof'),
                'payments/'.$this->organizationId(),
                'proof'
            );
        }

        $payment->update($data);

        if (! $wasPaid && $status === PaymentStatus::Paid) {
            $payment->loadMissing('tenant.user');
            $tenantUser = $payment->tenant?->user;

            if ($tenantUser) {
                $tenantUser->notify(new RentReceivedNotification($payment->fresh(['property:id,name'])));
            }
        }

        return redirect()
            ->route('landlord.payments.show', $payment)
            ->with('success', 'Payment updated.');
    }

    public function proof(RentPayment $payment): StreamedResponse
    {
        $this->authorize('view', $payment);

        if (! $payment->proof_path || ! Storage::disk('local')->exists($payment->proof_path)) {
            abort(404);
        }

        return Storage::disk('local')->response($payment->proof_path);
    }

    public function bulkDestroy(BulkDestroyPaymentsRequest $request): RedirectResponse
    {
        $this->authorize('viewAny', RentPayment::class);

        $ids = $request->validated('ids');

        $payments = RentPayment::query()
            ->forOrganization($this->organizationId())
            ->whereIn('id', $ids)
            ->get();

        $deleted = 0;

        foreach ($payments as $payment) {
            $this->authorize('delete', $payment);
            $payment->delete();
            $deleted++;
        }

        if ($deleted === 0) {
            return back()->with('error', 'No matching payments were deleted.');
        }

        return back()->with(
            'success',
            $deleted === 1
                ? '1 payment deleted.'
                : "{$deleted} payments deleted."
        );
    }

    public function downloadReceipt(RentPayment $payment): HttpResponse
    {
        $this->authorize('view', $payment);

        $payment->load(['tenant', 'property', 'lease', 'organization']);

        if ($payment->status !== PaymentStatus::Paid) {
            abort(422, 'Receipt is available only for paid payments.');
        }

        if (! $payment->receipt_number) {
            $payment->update(['receipt_number' => $this->nextReceiptNumber()]);
            $payment->refresh();
        }

        $pdf = Pdf::loadView('pdf.receipt', [
            'payment' => $payment,
            'organization' => $payment->organization,
        ]);

        $filename = 'receipt-'.($payment->receipt_number ?? $payment->id).'.pdf';

        return $pdf->download($filename);
    }

    protected function nextReceiptNumber(): string
    {
        $prefix = 'RN-'.now()->format('Ym').'-';
        $latest = RentPayment::query()
            ->where('receipt_number', 'like', $prefix.'%')
            ->orderByDesc('receipt_number')
            ->value('receipt_number');

        $sequence = 1;
        if ($latest && preg_match('/(\d+)$/', $latest, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    protected function proofIsImage(?string $path): bool
    {
        if ($path === null || $path === '') {
            return false;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
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
