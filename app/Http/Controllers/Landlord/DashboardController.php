<?php

namespace App\Http\Controllers\Landlord;

use App\Enums\LeaseStatus;
use App\Enums\MaintenanceStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Concerns\ResolvesOrganization;
use App\Http\Controllers\Controller;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Notice;
use App\Models\Property;
use App\Models\RentPayment;
use App\Models\Tenant;
use App\Models\Unit;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    use ResolvesOrganization;

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Property::class);

        $orgId = $this->organizationId();
        $user = $request->user();
        $hour = now()->hour;

        $greeting = match (true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default => 'Good evening',
        };

        $totalProperties = Property::query()->forOrganization($orgId)->count();
        $occupiedUnits = Unit::query()
            ->forOrganization($orgId)
            ->where('status', 'occupied')
            ->count();

        $monthlyRent = (float) Lease::query()
            ->forOrganization($orgId)
            ->where('status', LeaseStatus::Active)
            ->sum('monthly_rent');

        $pendingRent = (float) RentPayment::query()
            ->forOrganization($orgId)
            ->whereIn('status', [PaymentStatus::Pending, PaymentStatus::Late, PaymentStatus::Partial])
            ->selectRaw('SUM(amount - amount_paid) as outstanding')
            ->value('outstanding');

        $openMaintenance = MaintenanceRequest::query()
            ->forOrganization($orgId)
            ->whereNotIn('status', [MaintenanceStatus::Resolved, MaintenanceStatus::Closed])
            ->count();

        $hasTenants = Tenant::query()->forOrganization($orgId)->exists();

        $propertyOverview = Lease::query()
            ->forOrganization($orgId)
            ->whereIn('status', [LeaseStatus::Active, LeaseStatus::Expiring])
            ->with(['property:id,name,city', 'tenant:id,name', 'unit:id,name'])
            ->latest('start_date')
            ->limit(8)
            ->get()
            ->map(function (Lease $lease) {
                $dueDay = min(max((int) $lease->rent_due_day, 1), 28);
                $nextDue = now()->day <= $dueDay
                    ? now()->copy()->day($dueDay)
                    : now()->copy()->addMonthNoOverflow()->day($dueDay);

                return [
                    'id' => $lease->id,
                    'property' => $lease->property?->name,
                    'city' => $lease->property?->city,
                    'unit' => $lease->unit?->name,
                    'tenant' => $lease->tenant?->name,
                    'monthly_rent' => (float) $lease->monthly_rent,
                    'next_due' => $nextDue->toDateString(),
                    'status' => $lease->status?->value,
                    'status_label' => $lease->status?->label(),
                    'status_color' => $lease->status?->color(),
                ];
            });

        $recentPayments = RentPayment::query()
            ->forOrganization($orgId)
            ->with(['tenant:id,name', 'property:id,name'])
            ->latest('payment_date')
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(fn (RentPayment $payment) => [
                'id' => $payment->id,
                'tenant' => $payment->tenant?->name,
                'property' => $payment->property?->name,
                'amount' => (float) $payment->amount_paid ?: (float) $payment->amount,
                'status' => $payment->status?->value,
                'status_label' => $payment->status?->label(),
                'status_color' => $payment->status?->color(),
                'date' => optional($payment->payment_date ?? $payment->due_date)->toDateString(),
                'period_label' => $payment->period_label,
            ]);

        $recentMaintenance = MaintenanceRequest::query()
            ->forOrganization($orgId)
            ->with(['property:id,name', 'tenant:id,name'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (MaintenanceRequest $item) => [
                'id' => $item->id,
                'title' => $item->title,
                'property' => $item->property?->name,
                'tenant' => $item->tenant?->name,
                'priority' => $item->priority?->value,
                'priority_label' => $item->priority?->label(),
                'priority_color' => $item->priority?->color(),
                'status' => $item->status?->value,
                'status_label' => $item->status?->label(),
                'status_color' => $item->status?->color(),
                'created_at' => $item->created_at?->toDateString(),
            ]);

        $rentDues = RentPayment::query()
            ->forOrganization($orgId)
            ->whereIn('status', [PaymentStatus::Pending, PaymentStatus::Late, PaymentStatus::Partial])
            ->whereBetween('due_date', [now()->toDateString(), now()->addDays(14)->toDateString()])
            ->with(['tenant:id,name', 'property:id,name'])
            ->orderBy('due_date')
            ->limit(8)
            ->get()
            ->map(fn (RentPayment $payment) => [
                'id' => $payment->id,
                'label' => ($payment->tenant?->name ?? 'Tenant').' · '.($payment->property?->name ?? 'Property'),
                'date' => $payment->due_date?->toDateString(),
                'amount' => (float) $payment->amount - (float) $payment->amount_paid,
            ]);

        $leaseExpiries = Lease::query()
            ->forOrganization($orgId)
            ->whereIn('status', [LeaseStatus::Active, LeaseStatus::Expiring])
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [now()->toDateString(), now()->addDays(60)->toDateString()])
            ->with(['tenant:id,name', 'property:id,name'])
            ->orderBy('end_date')
            ->limit(8)
            ->get()
            ->map(fn (Lease $lease) => [
                'id' => $lease->id,
                'label' => ($lease->tenant?->name ?? 'Tenant').' · '.($lease->property?->name ?? 'Property'),
                'date' => $lease->end_date?->toDateString(),
            ]);

        $scheduledMaintenance = MaintenanceRequest::query()
            ->forOrganization($orgId)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>=', now())
            ->whereNotIn('status', [MaintenanceStatus::Resolved, MaintenanceStatus::Closed])
            ->with(['property:id,name'])
            ->orderBy('scheduled_at')
            ->limit(8)
            ->get()
            ->map(fn (MaintenanceRequest $item) => [
                'id' => $item->id,
                'label' => $item->title.' · '.($item->property?->name ?? 'Property'),
                'date' => $item->scheduled_at?->toDateString(),
            ]);

        $notices = Notice::query()
            ->forOrganization($orgId)
            ->whereDate('publish_date', '<=', now())
            ->where(function ($query) {
                $query->whereNull('expiry_date')
                    ->orWhereDate('expiry_date', '>=', now());
            })
            ->latest('publish_date')
            ->limit(5)
            ->get()
            ->map(fn (Notice $notice) => [
                'id' => $notice->id,
                'title' => $notice->title,
                'date' => $notice->publish_date?->toDateString(),
            ]);

        return Inertia::render('Landlord/Dashboard', [
            'greeting' => $greeting.', '.($user?->name ?? 'there'),
            'stats' => [
                'total_properties' => $totalProperties,
                'occupied_units' => $occupiedUnits,
                'monthly_rent' => $monthlyRent,
                'pending_rent' => (float) ($pendingRent ?? 0),
                'open_maintenance' => $openMaintenance,
            ],
            'propertyOverview' => $propertyOverview,
            'recentPayments' => $recentPayments,
            'recentMaintenance' => $recentMaintenance,
            'upcoming' => [
                'rent_dues' => $rentDues,
                'lease_expiries' => $leaseExpiries,
                'scheduled_maintenance' => $scheduledMaintenance,
                'notices' => $notices,
            ],
            'onboarding' => [
                'has_properties' => $totalProperties > 0,
                'has_tenants' => $hasTenants,
            ],
        ]);
    }
}
