<?php

namespace App\Services;

use App\Enums\LeaseStatus;
use App\Enums\PaymentStatus;
use App\Models\RentPayment;
use App\Models\User;
use App\Notifications\RentOverdueNotification;
use App\Notifications\RentReminderNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

class RentReminderService
{
    /**
     * Send rent reminders for pending/late payments on active leases.
     *
     * @return array{sent: int, skipped: int}
     */
    public function sendDueReminders(?Carbon $today = null): array
    {
        $today = ($today ?? now())->startOfDay();
        $sent = 0;
        $skipped = 0;

        $payments = RentPayment::query()
            ->whereIn('status', [PaymentStatus::Pending, PaymentStatus::Late, PaymentStatus::Partial])
            ->whereHas('lease', function ($query) {
                $query->whereIn('status', [LeaseStatus::Active, LeaseStatus::Expiring]);
            })
            ->with(['tenant.user', 'property:id,name', 'lease:id,status'])
            ->get();

        foreach ($payments as $payment) {
            if (! $payment->due_date) {
                $skipped++;

                continue;
            }

            $daysUntilDue = (int) $today->diffInDays($payment->due_date->copy()->startOfDay(), false);
            $reminderType = $this->reminderTypeForDays($daysUntilDue);

            if ($reminderType === null) {
                $skipped++;

                continue;
            }

            if (! $this->shouldSend($payment, $reminderType, $today)) {
                $skipped++;

                continue;
            }

            $user = $payment->tenant?->user;

            if (! $user instanceof User) {
                $skipped++;

                continue;
            }

            if ($reminderType === 'overdue') {
                Notification::send($user, new RentOverdueNotification($payment));
            } else {
                Notification::send($user, new RentReminderNotification($payment, $reminderType));
            }

            $this->markSent($payment, $reminderType, $today);
            $sent++;
        }

        return ['sent' => $sent, 'skipped' => $skipped];
    }

    protected function reminderTypeForDays(int $daysUntilDue): ?string
    {
        return match (true) {
            $daysUntilDue === 7 => '7_days',
            $daysUntilDue === 3 => '3_days',
            $daysUntilDue === 1 => '1_day',
            $daysUntilDue === 0 => 'due_date',
            $daysUntilDue <= -1 => 'overdue',
            default => null,
        };
    }

    protected function shouldSend(RentPayment $payment, string $reminderType, Carbon $today): bool
    {
        // Non-overdue reminders only match one calendar day; still guard against re-runs.
        return Cache::add($this->cacheKey($payment, $reminderType, $today), true, $today->copy()->endOfDay());
    }

    protected function markSent(RentPayment $payment, string $reminderType, Carbon $today): void
    {
        // Cache::add already stored the key in shouldSend(); keep method for clarity/extension.
        Cache::put($this->cacheKey($payment, $reminderType, $today), true, $today->copy()->endOfDay());
    }

    protected function cacheKey(RentPayment $payment, string $reminderType, Carbon $today): string
    {
        return sprintf(
            'rentnest:rent_reminder:%d:%s:%s',
            $payment->id,
            $reminderType,
            $today->toDateString()
        );
    }
}
