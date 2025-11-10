<?php
namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RevisionRequestedNotification extends Notification
{

    public function __construct(public Order $order, public string $feedback)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Revision Requested - Order #' . $this->order->id)
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('The client has requested revisions for Order #' . $this->order->id)
            ->line('Service: ' . $this->order->service->title)
            ->line('Feedback: ' . $this->feedback)
            ->action('View Order', route('orders.show', $this->order))
            ->line('Please review the feedback and submit revised work.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id'      => $this->order->id,
            'service_title' => $this->order->service->title,
            'feedback'      => $this->feedback,
            'message'       => 'Revision requested for Order #' . $this->order->id,
            'action_url'    => route('orders.show', $this->order),
        ];
    }
}
