<?php

namespace App\Policies;

use App\Enums\PaymentStatus;
use App\Models\RentPayment;
use App\Models\User;
use App\Policies\Concerns\ChecksOrganizationAccess;

class RentPaymentPolicy
{
    use ChecksOrganizationAccess;

    public function viewAny(User $user): bool
    {
        return $user->isLandlord() || $user->isTenant();
    }

    public function view(User $user, RentPayment $rentPayment): bool
    {
        if ($this->landlordInOrganization($user, $rentPayment)) {
            return true;
        }

        return $this->ownsPayment($user, $rentPayment);
    }

    public function downloadReceipt(User $user, RentPayment $rentPayment): bool
    {
        if (! $this->view($user, $rentPayment)) {
            return false;
        }

        return in_array($rentPayment->status, [PaymentStatus::Paid, PaymentStatus::Partial], true);
    }

    public function create(User $user): bool
    {
        return $user->isLandlord();
    }

    public function update(User $user, RentPayment $rentPayment): bool
    {
        return $this->landlordInOrganization($user, $rentPayment);
    }

    public function delete(User $user, RentPayment $rentPayment): bool
    {
        return $this->landlordInOrganization($user, $rentPayment);
    }

    protected function ownsPayment(User $user, RentPayment $rentPayment): bool
    {
        if (! $user->isTenant() || ! $this->sameOrganization($user, $rentPayment)) {
            return false;
        }

        $tenant = $user->tenantProfile;

        return $tenant !== null
            && (int) $rentPayment->tenant_id === (int) $tenant->id;
    }
}
