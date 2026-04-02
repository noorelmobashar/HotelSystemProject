<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InactiveClientReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('We miss you!')
            ->greeting('Hi '.$notifiable->name.',')
            ->line('We noticed that you haven\'t logged in for a while.')
            ->line('We miss you and would love to see you again.')
            ->line('Best regards,')
            ->line('Hotel Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'inactive_login_reminder',
            'subject' => 'We miss you!',
            'message' => 'We noticed that you haven\'t logged in for a while. We miss you and would love to see you again.',
        ];
    }
}
