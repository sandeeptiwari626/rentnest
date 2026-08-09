<?php

namespace App\Http\Controllers\Tenant;

use App\Enums\LeaseStatus;
use App\Http\Controllers\Concerns\ResolvesOrganization;
use App\Http\Controllers\Concerns\ResolvesTenantProfile;
use App\Http\Controllers\Controller;
use App\Models\Lease;
use App\Support\Money;
use Inertia\Inertia;
use Inertia\Response;

class MyHomeController extends Controller
{
    use ResolvesOrganization;
    use ResolvesTenantProfile;

    public function index(): Response
    {
        $tenant = $this->tenantProfile();
        $organizationId = $this->organizationId();

        $lease = Lease::query()
            ->forOrganization($organizationId)
            ->where('tenant_id', $tenant->id)
            ->whereIn('status', [LeaseStatus::Active, LeaseStatus::Expiring, LeaseStatus::Expired])
            ->with(['property', 'unit', 'organization'])
            ->orderByRaw("CASE status WHEN 'active' THEN 0 WHEN 'expiring' THEN 1 ELSE 2 END")
            ->orderByDesc('start_date')
            ->first();

        if ($lease) {
            $this->authorize('view', $lease);
        }

        $organization = $lease?->organization ?? $tenant->organization()->first();

        return Inertia::render('Tenant/MyHome', [
            'lease' => $lease ? [
                'id' => $lease->id,
                'start_date' => $lease->start_date?->toDateString(),
                'end_date' => $lease->end_date?->toDateString(),
                'monthly_rent' => (float) $lease->monthly_rent,
                'monthly_rent_formatted' => Money::format($lease->monthly_rent),
                'security_deposit' => (float) $lease->security_deposit,
                'security_deposit_formatted' => Money::format($lease->security_deposit),
                'rent_due_day' => $lease->rent_due_day,
                'notice_period_days' => $lease->notice_period_days,
                'status' => $lease->status?->value,
                'status_label' => $lease->status?->label(),
                'status_color' => $lease->status?->color(),
            ] : null,
            'property' => $lease?->property ? [
                'id' => $lease->property->id,
                'name' => $lease->property->name,
                'type' => $lease->property->type?->value,
                'type_label' => $lease->property->type?->label(),
                'address' => $lease->property->address,
                'city' => $lease->property->city,
                'state' => $lease->property->state,
                'postal_code' => $lease->property->postal_code,
                'full_address' => collect([
                    $lease->property->address,
                    $lease->property->city,
                    $lease->property->state,
                    $lease->property->postal_code,
                ])->filter()->implode(', '),
                'bedrooms' => $lease->property->bedrooms,
                'bathrooms' => $lease->property->bathrooms,
                'area' => $lease->property->area ? (float) $lease->property->area : null,
                'area_unit' => $lease->property->area_unit,
                'description' => $lease->property->description,
            ] : null,
            'unit' => $lease?->unit ? [
                'id' => $lease->unit->id,
                'name' => $lease->unit->name,
                'bedrooms' => $lease->unit->bedrooms,
                'bathrooms' => $lease->unit->bathrooms,
                'area' => $lease->unit->area ? (float) $lease->unit->area : null,
                'rent_amount' => $lease->unit->rent_amount ? (float) $lease->unit->rent_amount : null,
                'rent_amount_formatted' => $lease->unit->rent_amount
                    ? Money::format($lease->unit->rent_amount)
                    : null,
            ] : null,
            'organization' => $organization ? [
                'name' => $organization->name,
                'email' => $organization->email,
                'phone' => $organization->phone,
            ] : null,
            'tenant' => [
                'name' => $tenant->name,
                'email' => $tenant->email,
                'phone' => $tenant->phone,
                'emergency_contact_name' => $tenant->emergency_contact_name,
                'emergency_contact_phone' => $tenant->emergency_contact_phone,
            ],
        ]);
    }
}
