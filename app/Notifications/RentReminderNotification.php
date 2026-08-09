<?php

namespace App\Notifications;

use App\Models\RentPayment;
use App\Support\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RentReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public RentPayment $payment,
        public string $reminderType,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $payload = $this->payload();

        return (new MailMessage)
            ->subject($payload['title'])
            ->line($payload['message'])
            ->action('View payment', $payload['url']);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->payload();
    }

    /**
     * @return array{title: string, message: string, type: string, url: string, action: string, reminder_type: string, payment_id: int}
     */
    protected function payload(): array
    {
        $this->payment->loadMissing(['property:id,name', 'tenant:id,name']);

        $amount = Money::format($this->payment->amount);
        $dueDate = $this->payment->due_date?->format('d M Y') ?? 'soon';
        $period = $this->payment->period_label ?: 'rent';
        $property = $this->payment->property?->name ?? 'your property';

        [$title, $message] = match ($this->reminderType) {
            '7_days' => [
                'Rent due in 7 days',
                "Reminder: {$amount} for {$period} at {$property} is due on {$dueDate}.",
            ],
            '3_days' => [
                'Rent due in 3 days',
                "Reminder: {$amount} for {$period} at {$property} is due on {$dueDate}.",
            ],
            '1_day' => [
                'Rent due tomorrow',
                "Reminder: {$amount} for {$period} at {$property} is due tomorrow ({$dueDate}).",
            ],
            'due_date' => [
                'Rent is due today',
                "Today is the due date for {$amount} ({$period}) at {$property}.",
            ],
            'overdue' => [
                'Rent overdue',
                "Your rent of {$amount} for {$period} at {$property} was due on {$dueDate} and is still unpaid.",
            ],
            default => [
                'Rent reminder',
                "Reminder: {$amount} for {$period} at {$property} is due on {$dueDate}.",
            ],
        };

        return [
            'title' => $title,
            'message' => $message,
            'type' => 'rent_reminder',
            'url' => route('tenant.payments.show', $this->payment),
            'action' => 'View payment',
            'reminder_type' => $this->reminderType,
            'payment_id' => $this->payment->id,
        ];
    }
}
