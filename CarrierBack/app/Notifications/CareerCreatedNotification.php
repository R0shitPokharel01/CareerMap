<?php

namespace App\Notifications;

use App\Models\Careers;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CareerCreatedNotification extends Notification
{
    use Queueable;

    protected Careers $career;

    public function __construct(Careers $career)
    {
        $this->career = $career;
    }

    /**
     * Notification channels.
     */
    public function via(object $notifiable): array
    {
        return ['database']; // Add 'mail' if you also want email notifications
    }

    /**
     * Store in notifications table.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'New Career Created',
            'career_id' => $this->career->id,
            'career_title' => $this->career->title,
            'message' => "A new career '{$this->career->title}' has been created.",
            'created_by' => $this->career->user_id,
        ];
    }

    /**
     * email notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Career Created')
            ->greeting('Hello!')
            ->line("A new career '{$this->career->title}' has been created.")
            ->action('View Career', url('/admin/careers/' . $this->career->id));
    }

    /**
     * Array representation.
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
