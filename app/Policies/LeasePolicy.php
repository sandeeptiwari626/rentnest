<?php

namespace App\Policies;

use App\Models\Lease;
use App\Models\User;
use App\Policies\Concerns\ChecksOrganizationAccess;

class LeasePolicy
{
    use ChecksOrganizationAccess;

    public function viewAny(User $user): bool
    {
        return $user->isLandlord();
    }

    public function view(User $user, Lease $lease): bool
    {
        if ($this->landlordInOrganization($user, $lease)) {
            return true;
        }

        if (! $user->isTenant() || ! $this->sameOrganization($user, $lease)) {
            return false;
        }

        $tenant = $user->tenantProfile;

        return $tenant !== null
            && (int) $lease->tenant_id === (int) $tenant->id;
    }

    public function create(User $user): bool
    {
        return $user->isLandlord();
    }

    public function update(User $user, Lease $lease): bool
    {
        return $this->landlordInOrganization($user, $lease);
    }

    public function delete(User $user, Lease $lease): bool
    {
        return $this->landlordInOrganization($user, $lease);
    }
}
