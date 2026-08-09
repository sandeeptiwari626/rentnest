<?php

namespace App\Policies;

use App\Models\Lease;
use App\Models\Notice;
use App\Models\User;
use App\Policies\Concerns\ChecksOrganizationAccess;

class NoticePolicy
{
    use ChecksOrganizationAccess;

    public function viewAny(User $user): bool
    {
        return $user->isLandlord() || $user->isTenant();
    }

    public function view(User $user, Notice $notice): bool
    {
        if ($this->landlordInOrganization($user, $notice)) {
            return true;
        }

        if (! $user->isTenant() || ! $this->sameOrganization($user, $notice)) {
            return false;
        }

        return $this->noticeVisibleToTenant($user, $notice);
    }

    public function create(User $user): bool
    {
        return $user->isLandlord();
    }

    public function update(User $user, Notice $notice): bool
    {
        return $this->landlordInOrganization($user, $notice);
    }

    public function delete(User $user, Notice $notice): bool
    {
        return $this->landlordInOrganization($user, $notice);
    }

    protected function noticeVisibleToTenant(User $user, Notice $notice): bool
    {
        $tenant = $user->tenantProfile;

        if ($tenant === null) {
            return false;
        }

        // Org-wide notice
        if ($notice->property_id === null && $notice->tenant_id === null) {
            return true;
        }

        // Targeted at this tenant
        if ($notice->tenant_id !== null && (int) $notice->tenant_id === (int) $tenant->id) {
            return true;
        }

        // Targeted at a property the tenant has leased
        if ($notice->property_id !== null) {
            return Lease::query()
                ->where('tenant_id', $tenant->id)
                ->where('property_id', $notice->property_id)
                ->exists();
        }

        return false;
    }
}
