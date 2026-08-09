<?php

namespace App\Policies;

use App\Models\Unit;
use App\Models\User;
use App\Policies\Concerns\ChecksOrganizationAccess;

class UnitPolicy
{
    use ChecksOrganizationAccess;

    public function viewAny(User $user): bool
    {
        return $user->isLandlord();
    }

    public function view(User $user, Unit $unit): bool
    {
        return $this->landlordInOrganization($user, $unit);
    }

    public function create(User $user): bool
    {
        return $user->isLandlord();
    }

    public function update(User $user, Unit $unit): bool
    {
        return $this->landlordInOrganization($user, $unit);
    }

    public function delete(User $user, Unit $unit): bool
    {
        return $this->landlordInOrganization($user, $unit);
    }
}
