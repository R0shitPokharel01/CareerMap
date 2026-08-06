<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminMessageNotification extends Notification
{
    use Queueable;

    public function __construct(
        private string $title,
        private string $message,
        private string $notificationType,
        private int $sentBy
    ) {}

    public function via(object $notifiable): array
    {
        return [
            'mail',
            'database'
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title)
            ->greeting('Hello ' . $notifiable->name)
            ->line($this->message)
            ->line('Sent by Career Roadmap Admin.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'notification_type' => $this->notificationType,
            'sent_by' => $this->sentBy,
        ];
    }
}
