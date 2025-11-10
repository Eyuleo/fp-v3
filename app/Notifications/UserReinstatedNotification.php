<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserReinstatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Account Has Been Reinstated')
            ->line('Good news! Your account has been reinstated.')
            ->line('You can now access all platform features again.')
            ->action('Go to Dashboard', url('/'))
            ->line('Thank you for being part of our community!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Your account has been reinstated. Welcome back!',
            'type'    => 'account_reinstated',
        ];
    }
}
