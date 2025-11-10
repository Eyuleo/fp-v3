<?php
namespace App\Notifications;

use App\Models\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServiceDisabledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Service $service,
        public string $reason
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Service Has Been Disabled')
            ->line('Your service "' . $this->service->title . '" has been disabled by the platform administrators.')
            ->line('Reason: ' . $this->reason)
            ->line('If you have questions or believe this is a mistake, please contact support.')
            ->line('Thank you for your understanding.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message'    => 'Your service "' . $this->service->title . '" has been disabled.',
            'reason'     => $this->reason,
            'service_id' => $this->service->id,
            'type'       => 'service_disabled',
        ];
    }
}
