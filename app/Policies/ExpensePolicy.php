<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;
use App\Policies\Concerns\ChecksOrganizationAccess;

class ExpensePolicy
{
    use ChecksOrganizationAccess;

    public function viewAny(User $user): bool
    {
        return $user->isLandlord();
    }

    public function view(User $user, Expense $expense): bool
    {
        return $this->landlordInOrganization($user, $expense);
    }

    public function create(User $user): bool
    {
        return $user->isLandlord();
    }

    public function update(User $user, Expense $expense): bool
    {
        return $this->landlordInOrganization($user, $expense);
    }

    public function delete(User $user, Expense $expense): bool
    {
        return $this->landlordInOrganization($user, $expense);
    }
}
