<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentUploadedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Document $document) {}

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
     * @return array{title: string, message: string, type: string, url: string, action: string, document_id: int}
     */
    protected function payload(): array
    {
        $type = $this->document->type?->label() ?? 'Document';

        return [
            'title' => 'New document available',
            'message' => "{$type}: \"{$this->document->title}\" has been shared with you.",
            'type' => 'document_uploaded',
            'url' => route('tenant.documents.index'),
            'action' => 'View documents',
            'document_id' => $this->document->id,
        ];
    }
}
