<?php

namespace App\Notifications;

use App\Models\MaintenanceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MaintenanceUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public MaintenanceRequest $maintenance) {}

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
     * @return array{title: string, message: string, type: string, url: string, action: string, maintenance_id: int, status: string|null}
     */
    protected function payload(): array
    {
        $status = $this->maintenance->status?->label() ?? 'updated';

        return [
            'title' => 'Maintenance request updated',
            'message' => "\"{$this->maintenance->title}\" is now marked as {$status}.",
            'type' => 'maintenance_updated',
            'url' => route('tenant.maintenance.show', $this->maintenance),
            'action' => 'View request',
            'maintenance_id' => $this->maintenance->id,
            'status' => $this->maintenance->status?->value,
        ];
    }
}
