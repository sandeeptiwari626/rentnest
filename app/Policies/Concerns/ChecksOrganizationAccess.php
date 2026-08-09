<?php

namespace App\Policies\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

trait ChecksOrganizationAccess
{
    protected function sameOrganization(User $user, Model $model): bool
    {
        $organizationId = $model->getAttribute('organization_id');

        if ($organizationId === null) {
            return false;
        }

        if ((int) $user->current_organization_id === (int) $organizationId) {
            return true;
        }

        return $user->organizations()->where('organizations.id', $organizationId)->exists();
    }

    protected function landlordInOrganization(User $user, Model $model): bool
    {
        return $user->isLandlord() && $this->sameOrganization($user, $model);
    }
}
