<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\LeaseStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Concerns\ResolvesOrganization;
use App\Http\Controllers\Concerns\ResolvesTenantProfile;
use App\Http\Controllers\Controller;
use App\Models\Lease;
use App\Models\RentPayment;
use App\Support\Money;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    use ResolvesOrganization;
    use ResolvesTenantProfile;

    public function index(): Response
    {
        $this->authorize('viewAny', RentPayment::class);

        $tenant = $this->tenantProfile();
        $organizationId = $this->organizationId();

        $lease = Lease::query()
            ->forOrganization($organizationId)
            ->where('tenant_id', $tenant->id)
            ->whereIn('status', [LeaseStatus::Active, LeaseStatus::Expiring])
            ->with('property')
            ->orderByDesc('start_date')
            ->first();

        $current = RentPayment::query()
            ->forOrganization($organizationId)
            ->where('tenant_id', $tenant->id)
            ->whereIn('status', [PaymentStatus::Pending, PaymentStatus::Late, PaymentStatus::Partial])
            ->orderBy('due_date')
            ->first();

        $payments = RentPayment::query()
            ->forOrganization($organizationId)
            ->where('tenant_id', $tenant->id)
            ->with('property:id,name')
            ->orderByDesc('due_date')
            ->paginate(12)
            ->through(fn (RentPayment $payment) => [
                'id' => $payment->id,
                'period_label' => $payment->period_label,
                'amount' => (float) $payment->amount,
                'amount_formatted' => Money::format($payment->amount),
                'amount_paid' => (float) $payment->amount_paid,
                'amount_paid_formatted' => Money::format($payment->amount_paid),
                'due_date' => $payment->due_date?->toDateString(),
                'payment_date' => $payment->payment_date?->toDateString(),
                'status' => $payment->status?->value,
                'status_label' => $payment->status?->label(),
                'status_color' => $payment->status?->color(),
                'property_name' => $payment->property?->name,
                'receipt_number' => $payment->receipt_number,
            ]);

        return Inertia::render('Tenant/Payments/Index', [
            'currentRent' => [
                'monthly_rent' => $lease ? (float) $lease->monthly_rent : null,
                'monthly_rent_formatted' => $lease ? Money::format($lease->monthly_rent) : null,
                'rent_due_day' => $lease?->rent_due_day,
                'property_name' => $lease?->property?->name,
            ],
            'nextDue' => $current ? [
                'id' => $current->id,
                'amount' => (float) $current->amount,
                'amount_formatted' => Money::format($current->amount),
                'due_date' => $current->due_date?->toDateString(),
                'period_label' => $current->period_label,
                'status' => $current->status?->value,
                'status_label' => $current->status?->label(),
                'status_color' => $current->status?->color(),
            ] : null,
            'payments' => $payments,
        ]);
    }

    public function show(RentPayment $payment): Response
    {
        $this->ensureTenantPayment($payment);
        $this->authorize('view', $payment);

        $payment->load(['property:id,name,address,city', 'lease.unit:id,name', 'organization:id,name,email,phone']);

        return Inertia::render('Tenant/Payments/Show', [
            'payment' => [
                'id' => $payment->id,
                'period_label' => $payment->period_label,
                'amount' => (float) $payment->amount,
                'amount_formatted' => Money::format($payment->amount),
                'amount_paid' => (float) $payment->amount_paid,
                'amount_paid_formatted' => Money::format($payment->amount_paid),
                'due_date' => $payment->due_date?->toDateString(),
                'payment_date' => $payment->payment_date?->toDateString(),
                'payment_method' => $payment->payment_method?->value,
                'payment_method_label' => $payment->payment_method?->label(),
                'reference_number' => $payment->reference_number,
                'receipt_number' => $payment->receipt_number,
                'status' => $payment->status?->value,
                'status_label' => $payment->status?->label(),
                'status_color' => $payment->status?->color(),
                'notes' => $payment->notes,
                'property_name' => $payment->property?->name,
                'property_address' => collect([
                    $payment->property?->address,
                    $payment->property?->city,
                ])->filter()->implode(', '),
                'unit_name' => $payment->lease?->unit?->name,
                'can_download_receipt' => in_array($payment->status, [PaymentStatus::Paid, PaymentStatus::Partial], true),
            ],
        ]);
    }

    public function downloadReceipt(RentPayment $payment): HttpResponse
    {
        $this->ensureTenantPayment($payment);
        $this->authorize('downloadReceipt', $payment);

        $payment->load([
            'tenant',
            'property',
            'lease.unit',
            'organization',
        ]);

        if (! $payment->receipt_number) {
            $payment->forceFill([
                'receipt_number' => 'RN-'.now()->format('Ym').'-'.str_pad((string) $payment->id, 4, '0', STR_PAD_LEFT),
            ])->save();
        }

        $pdf = Pdf::loadView('pdf.receipt', [
            'payment' => $payment,
            'organization' => $payment->organization,
        ]);

        $filename = 'receipt-'.($payment->receipt_number ?? $payment->id).'.pdf';

        return $pdf->download($filename);
    }

    protected function ensureTenantPayment(RentPayment $payment): void
    {
        $tenant = $this->tenantProfile();

        if ((int) $payment->organization_id !== $this->organizationId()
            || (int) $payment->tenant_id !== (int) $tenant->id
        ) {
            abort(404);
        }
    }
}
