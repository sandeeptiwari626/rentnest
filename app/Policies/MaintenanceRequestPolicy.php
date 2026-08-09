<?php

namespace App\Policies;

use App\Enums\MaintenanceStatus;
use App\Models\MaintenanceRequest;
use App\Models\User;
use App\Policies\Concerns\ChecksOrganizationAccess;

class MaintenanceRequestPolicy
{
    use ChecksOrganizationAccess;

    public function viewAny(User $user): bool
    {
        return $user->isLandlord() || $user->isTenant();
    }

    public function view(User $user, MaintenanceRequest $maintenanceRequest): bool
    {
        if ($this->landlordInOrganization($user, $maintenanceRequest)) {
            return true;
        }

        return $this->ownsRequest($user, $maintenanceRequest);
    }

    public function create(User $user): bool
    {
        return $user->isLandlord() || $user->isTenant();
    }

    public function update(User $user, MaintenanceRequest $maintenanceRequest): bool
    {
        if ($this->landlordInOrganization($user, $maintenanceRequest)) {
            return true;
        }

        return $this->ownsRequest($user, $maintenanceRequest)
            && $maintenanceRequest->status === MaintenanceStatus::Open;
    }

    public function delete(User $user, MaintenanceRequest $maintenanceRequest): bool
    {
        return $this->landlordInOrganization($user, $maintenanceRequest);
    }

    public function comment(User $user, MaintenanceRequest $maintenanceRequest): bool
    {
        if ($this->landlordInOrganization($user, $maintenanceRequest)) {
            return true;
        }

        return $this->ownsRequest($user, $maintenanceRequest);
    }

    protected function ownsRequest(User $user, MaintenanceRequest $maintenanceRequest): bool
    {
        if (! $user->isTenant() || ! $this->sameOrganization($user, $maintenanceRequest)) {
            return false;
        }

        if ((int) $maintenanceRequest->reported_by === (int) $user->id) {
            return true;
        }

        $tenant = $user->tenantProfile;

        return $tenant !== null
            && (int) $maintenanceRequest->tenant_id === (int) $tenant->id;
    }
}
