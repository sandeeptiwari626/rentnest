<?php

namespace App\Console\Commands;

use App\Services\RentReminderService;
use Illuminate\Console\Command;

class SendRentRemindersCommand extends Command
{
    protected $signature = 'rentnest:send-rent-reminders';

    protected $description = 'Send rent due and overdue reminders to tenants';

    public function handle(RentReminderService $service): int
    {
        $result = $service->sendDueReminders();

        $this->info("Rent reminders sent: {$result['sent']} (skipped: {$result['skipped']}).");

        return self::SUCCESS;
    }
}
