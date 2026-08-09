<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;
use App\Policies\Concerns\ChecksOrganizationAccess;

class PropertyPolicy
{
    use ChecksOrganizationAccess;

    public function viewAny(User $user): bool
    {
        return $user->isLandlord();
    }

    public function view(User $user, Property $property): bool
    {
        return $this->landlordInOrganization($user, $property);
    }

    public function create(User $user): bool
    {
        return $user->isLandlord();
    }

    public function update(User $user, Property $property): bool
    {
        return $this->landlordInOrganization($user, $property);
    }

    public function delete(User $user, Property $property): bool
    {
        return $this->landlordInOrganization($user, $property);
    }
}
