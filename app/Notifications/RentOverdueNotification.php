<?php

namespace App\Notifications;

use App\Models\RentPayment;
use App\Support\Money;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RentOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public RentPayment $payment) {}

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
            ->action($payload['action'], $payload['url']);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return $this->payload();
    }

    /**
     * @return array{title: string, message: string, type: string, url: string, action: string, payment_id: int}
     */
    protected function payload(): array
    {
        $this->payment->loadMissing(['property:id,name']);

        $amount = Money::format($this->payment->amount);
        $dueDate = $this->payment->due_date?->format('d M Y') ?? 'an earlier date';
        $period = $this->payment->period_label ?: 'rent';
        $property = $this->payment->property?->name ?? 'your property';

        return [
            'title' => 'Rent payment overdue',
            'message' => "Your rent of {$amount} for {$period} at {$property} was due on {$dueDate} and remains unpaid.",
            'type' => 'rent_overdue',
            'url' => route('tenant.payments.show', $this->payment),
            'action' => 'View payment',
            'payment_id' => $this->payment->id,
        ];
    }
}
