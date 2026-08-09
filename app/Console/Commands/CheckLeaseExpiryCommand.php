<?php

namespace App\Console\Commands;

use App\Enums\LeaseStatus;
use App\Enums\OrganizationRole;
use App\Models\Lease;
use App\Models\User;
use App\Notifications\LeaseExpiryNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

class CheckLeaseExpiryCommand extends Command
{
    protected $signature = 'rentnest:check-lease-expiry';

    protected $description = 'Mark leases as expiring within 60 days and notify landlords';

    public function handle(): int
    {
        $today = now()->startOfDay();
        $horizon = $today->copy()->addDays(60);
        $notified = 0;
        $marked = 0;

        $leases = Lease::query()
            ->whereIn('status', [LeaseStatus::Active, LeaseStatus::Expiring])
            ->whereNotNull('end_date')
            ->whereDate('end_date', '>=', $today)
            ->whereDate('end_date', '<=', $horizon)
            ->with(['property:id,name', 'unit:id,name', 'tenant:id,name', 'organization.users'])
            ->get();

        foreach ($leases as $lease) {
            if ($lease->status === LeaseStatus::Active) {
                $lease->update(['status' => LeaseStatus::Expiring]);
                $marked++;
            }

            $cacheKey = sprintf(
                'rentnest:lease_expiry:%d:%s',
                $lease->id,
                $today->toDateString()
            );

            if (! Cache::add($cacheKey, true, $today->copy()->endOfDay())) {
                continue;
            }

            $landlords = $this->landlordsFor($lease);

            if ($landlords->isEmpty()) {
                continue;
            }

            Notification::send($landlords, new LeaseExpiryNotification($lease));
            $notified++;
        }

        $this->info("Leases marked expiring: {$marked}. Landlord notifications: {$notified}.");

        return self::SUCCESS;
    }

    /**
     * @return \Illuminate\Support\Collection<int, User>
     */
    protected function landlordsFor(Lease $lease)
    {
        return ($lease->organization?->users ?? collect())
            ->filter(function (User $user) {
                $role = $user->pivot->role;

                return ($role instanceof OrganizationRole
                    ? $role
                    : OrganizationRole::tryFrom((string) $role)) === OrganizationRole::Landlord;
            })
            ->values();
    }
}
