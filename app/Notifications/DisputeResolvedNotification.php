<?php
namespace App\Notifications;

use App\Models\Dispute;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DisputeResolvedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Dispute $dispute,
        public string $resolutionType
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Dispute Resolved - Order #' . $this->dispute->order_id)
            ->line('The dispute for Order #' . $this->dispute->order_id . ' has been resolved by an administrator.');

        if ($this->resolutionType === 'release') {
            $message->line('Resolution: Payment has been released to the student.');
        } elseif ($this->resolutionType === 'refund') {
            $message->line('Resolution: Full refund has been issued to the client.');
        } else {
            $message->line('Resolution: Partial resolution has been applied.');
        }

        return $message
            ->line('Resolution Notes: ' . $this->dispute->resolution_notes)
            ->action('View Order', route('orders.show', $this->dispute->order_id))
            ->line('Thank you for your patience.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message'         => 'Dispute for Order #' . $this->dispute->order_id . ' has been resolved.',
            'order_id'        => $this->dispute->order_id,
            'resolution_type' => $this->resolutionType,
            'type'            => 'dispute_resolved',
        ];
    }
}
