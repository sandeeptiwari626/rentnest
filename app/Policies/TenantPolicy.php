<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;
use App\Policies\Concerns\ChecksOrganizationAccess;

class TenantPolicy
{
    use ChecksOrganizationAccess;

    public function viewAny(User $user): bool
    {
        return $user->isLandlord();
    }

    public function view(User $user, Tenant $tenant): bool
    {
        return $this->landlordInOrganization($user, $tenant);
    }

    public function create(User $user): bool
    {
        return $user->isLandlord();
    }

    public function update(User $user, Tenant $tenant): bool
    {
        return $this->landlordInOrganization($user, $tenant);
    }

    public function delete(User $user, Tenant $tenant): bool
    {
        return $this->landlordInOrganization($user, $tenant);
    }
}
