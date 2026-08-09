<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\LeaseStatus;
use App\Enums\MaintenanceStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Concerns\ResolvesOrganization;
use App\Http\Controllers\Concerns\ResolvesTenantProfile;
use App\Http\Controllers\Controller;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Models\Notice;
use App\Models\RentPayment;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    use ResolvesOrganization;
    use ResolvesTenantProfile;

    public function index(Request $request): Response
    {
        $user = $request->user();
        $tenant = $this->tenantProfile();
        $organizationId = $this->organizationId();

        $lease = $this->activeLease($tenant->id, $organizationId);

        $nextPayment = RentPayment::query()
            ->forOrganization($organizationId)
            ->where('tenant_id', $tenant->id)
            ->whereIn('status', [PaymentStatus::Pending, PaymentStatus::Late, PaymentStatus::Partial])
            ->orderBy('due_date')
            ->first();

        $unpaidCount = RentPayment::query()
            ->forOrganization($organizationId)
            ->where('tenant_id', $tenant->id)
            ->whereIn('status', [PaymentStatus::Pending, PaymentStatus::Late, PaymentStatus::Partial])
            ->count();

        $openMaintenanceCount = MaintenanceRequest::query()
            ->forOrganization($organizationId)
            ->where('tenant_id', $tenant->id)
            ->whereNotIn('status', [MaintenanceStatus::Resolved, MaintenanceStatus::Closed])
            ->count();

        $unreadNotices = $this->visibleNoticesQuery($tenant, $organizationId)
            ->whereDoesntHave('reads', fn ($q) => $q->where('user_id', $user->id))
            ->count();

        $recentPayments = RentPayment::query()
            ->forOrganization($organizationId)
            ->where('tenant_id', $tenant->id)
            ->latest('due_date')
            ->limit(3)
            ->get()
            ->map(fn (RentPayment $payment) => [
                'id' => $payment->id,
                'period_label' => $payment->period_label,
                'amount' => (float) $payment->amount,
                'amount_formatted' => Money::format($payment->amount),
                'due_date' => $payment->due_date?->toDateString(),
                'status' => $payment->status?->value,
                'status_label' => $payment->status?->label(),
                'status_color' => $payment->status?->color(),
            ]);

        $recentMaintenance = MaintenanceRequest::query()
            ->forOrganization($organizationId)
            ->where('tenant_id', $tenant->id)
            ->latest()
            ->limit(3)
            ->get()
            ->map(fn (MaintenanceRequest $item) => [
                'id' => $item->id,
                'title' => $item->title,
                'status' => $item->status?->value,
                'status_label' => $item->status?->label(),
                'status_color' => $item->status?->color(),
                'priority' => $item->priority?->value,
                'created_at' => $item->created_at?->toIso8601String(),
            ]);

        $recentNotices = $this->visibleNoticesQuery($tenant, $organizationId)
            ->withExists(['reads as is_read' => fn ($q) => $q->where('user_id', $user->id)])
            ->orderByDesc('publish_date')
            ->limit(3)
            ->get()
            ->map(fn (Notice $notice) => [
                'id' => $notice->id,
                'title' => $notice->title,
                'publish_date' => $notice->publish_date?->toDateString(),
                'is_read' => (bool) $notice->is_read,
            ]);

        $activity = collect()
            ->merge($recentPayments->map(fn ($p) => [
                'type' => 'payment',
                'title' => $p['period_label'] ? "Rent · {$p['period_label']}" : 'Rent payment',
                'description' => $p['status_label'].' · '.$p['amount_formatted'],
                'date' => $p['due_date'],
                'href' => route('tenant.payments.show', $p['id']),
                'status' => $p['status_color'] ?? 'default',
            ]))
            ->merge($recentMaintenance->map(fn ($m) => [
                'type' => 'maintenance',
                'title' => $m['title'],
                'description' => $m['status_label'],
                'date' => $m['created_at'] ? Carbon::parse($m['created_at'])->toDateString() : null,
                'href' => route('tenant.maintenance.show', $m['id']),
                'status' => $m['status_color'] ?? 'default',
            ]))
            ->merge($recentNotices->map(fn ($n) => [
                'type' => 'notice',
                'title' => $n['title'],
                'description' => $n['is_read'] ? 'Notice' : 'Unread notice',
                'date' => $n['publish_date'],
                'href' => route('tenant.notices.show', $n['id']),
                'status' => $n['is_read'] ? 'default' : 'info',
            ]))
            ->sortByDesc('date')
            ->take(8)
            ->values();

        $hour = now()->hour;
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');

        return Inertia::render('Tenant/Home', [
            'greeting' => $greeting,
            'userName' => $user->name,
            'home' => $lease ? [
                'property_name' => $lease->property?->name,
                'unit_name' => $lease->unit?->name,
                'address' => collect([
                    $lease->property?->address,
                    $lease->property?->city,
                    $lease->property?->state,
                ])->filter()->implode(', '),
                'monthly_rent' => (float) $lease->monthly_rent,
                'monthly_rent_formatted' => Money::format($lease->monthly_rent),
                'rent_due_day' => $lease->rent_due_day,
                'lease_status' => $lease->status?->value,
                'lease_status_label' => $lease->status?->label(),
            ] : null,
            'nextPayment' => $nextPayment ? [
                'id' => $nextPayment->id,
                'amount' => (float) $nextPayment->amount,
                'amount_formatted' => Money::format($nextPayment->amount),
                'due_date' => $nextPayment->due_date?->toDateString(),
                'period_label' => $nextPayment->period_label,
                'status' => $nextPayment->status?->value,
                'status_label' => $nextPayment->status?->label(),
                'status_color' => $nextPayment->status?->color(),
            ] : null,
            'alerts' => [
                'unpaid_count' => $unpaidCount,
                'open_maintenance_count' => $openMaintenanceCount,
                'unread_notices_count' => $unreadNotices,
            ],
            'activity' => $activity,
        ]);
    }

    protected function activeLease(int $tenantId, int $organizationId): ?Lease
    {
        return Lease::query()
            ->forOrganization($organizationId)
            ->where('tenant_id', $tenantId)
            ->whereIn('status', [LeaseStatus::Active, LeaseStatus::Expiring])
            ->with(['property', 'unit'])
            ->orderByDesc('start_date')
            ->first();
    }

    /**
     * @param  \App\Models\Tenant  $tenant
     * @return \Illuminate\Database\Eloquent\Builder<Notice>
     */
    protected function visibleNoticesQuery($tenant, int $organizationId)
    {
        $propertyIds = Lease::query()
            ->where('tenant_id', $tenant->id)
            ->pluck('property_id');

        return Notice::query()
            ->forOrganization($organizationId)
            ->whereDate('publish_date', '<=', now())
            ->where(function ($q) {
                $q->whereNull('expiry_date')
                    ->orWhereDate('expiry_date', '>=', now());
            })
            ->where(function ($q) use ($tenant, $propertyIds) {
                $q->where(function ($inner) {
                    $inner->whereNull('tenant_id')->whereNull('property_id');
                })
                    ->orWhere('tenant_id', $tenant->id)
                    ->orWhereIn('property_id', $propertyIds);
            });
    }
}
