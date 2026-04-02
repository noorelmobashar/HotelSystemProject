<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClientApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome!')
            ->greeting('Dear '.$notifiable->name.',')
            ->line('Your account has been approved successfully.')
            ->line('We are happy to have you with us.')
            ->line('Best regards,')
            ->line('Hotel Management Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'client_approved',
            'subject' => 'Welcome!',
            'message' => 'Your account has been approved successfully. We are happy to have you with us.',
        ];
    }
}
