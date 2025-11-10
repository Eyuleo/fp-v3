<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserSuspendedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Account Has Been Suspended')
            ->line('Your account has been suspended by the platform administrators.')
            ->line('If you believe this is a mistake, please contact support.')
            ->line('Thank you for your understanding.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Your account has been suspended. Please contact support if you believe this is a mistake.',
            'type'    => 'account_suspended',
        ];
    }
}
