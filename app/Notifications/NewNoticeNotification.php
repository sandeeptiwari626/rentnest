<?php

namespace App\Notifications;

use App\Models\Notice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewNoticeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Notice $notice) {}

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
     * @return array{title: string, message: string, type: string, url: string, action: string, notice_id: int}
     */
    protected function payload(): array
    {
        $preview = str($this->notice->message)->stripTags()->limit(120)->toString();

        return [
            'title' => 'New notice: '.$this->notice->title,
            'message' => $preview !== '' ? $preview : 'A new notice has been published for you.',
            'type' => 'new_notice',
            'url' => route('tenant.notices.show', $this->notice),
            'action' => 'Read notice',
            'notice_id' => $this->notice->id,
        ];
    }
}
