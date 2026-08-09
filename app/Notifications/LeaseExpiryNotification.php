<?php

namespace App\Notifications;

use App\Models\Lease;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaseExpiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Lease $lease) {}

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
     * @return array{title: string, message: string, type: string, url: string, action: string, lease_id: int}
     */
    protected function payload(): array
    {
        $this->lease->loadMissing(['property:id,name', 'unit:id,name', 'tenant:id,name']);

        $property = $this->lease->property?->name ?? 'Property';
        $unit = $this->lease->unit?->name ?? 'unit';
        $tenant = $this->lease->tenant?->name ?? 'tenant';
        $endDate = $this->lease->end_date?->format('d M Y') ?? 'soon';

        return [
            'title' => 'Lease expiring soon',
            'message' => "Lease for {$tenant} at {$property} ({$unit}) expires on {$endDate}.",
            'type' => 'lease_expiry',
            'url' => route('landlord.leases.show', $this->lease),
            'action' => 'View lease',
            'lease_id' => $this->lease->id,
        ];
    }
}
