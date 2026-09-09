<?php

namespace App\Policies;

use App\Models\TenantPortalNotice;
use App\Models\User;
use App\Policies\Concerns\ChecksOrganizationAccess;

class TenantPortalNoticePolicy
{
    use ChecksOrganizationAccess;

    public function viewAny(User $user): bool
    {
        return $user->isLandlord() || $user->isTenant();
    }

    public function view(User $user, TenantPortalNotice $tenantPortalNotice): bool
    {
        if ($this->landlordInOrganization($user, $tenantPortalNotice)) {
            return true;
        }

        return $user->isTenant() && $this->sameOrganization($user, $tenantPortalNotice);
    }

    public function create(User $user): bool
    {
        return $user->isLandlord();
    }

    public function update(User $user, TenantPortalNotice $tenantPortalNotice): bool
    {
        return $this->landlordInOrganization($user, $tenantPortalNotice);
    }

    public function acknowledge(User $user, TenantPortalNotice $tenantPortalNotice): bool
    {
        return $user->isTenant() && $this->sameOrganization($user, $tenantPortalNotice);
    }
}
