<?php
namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkDeliveredNotification extends Notification
{

    public function __construct(public Order $order)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Work Delivered - Order #' . $this->order->id)
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('The student has delivered your work for: ' . $this->order->service->title)
            ->action('Review Delivery', route('orders.show', $this->order))
            ->line('Please review the delivery and either approve it or request revisions.')
            ->line('If you don\'t respond within 5 days, the order will be automatically approved.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id'      => $this->order->id,
            'service_title' => $this->order->service->title,
            'message'       => 'Work delivered for ' . $this->order->service->title,
            'action_url'    => route('orders.show', $this->order),
        ];
    }
}
