<?php
namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderAcceptedNotification extends Notification
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
            ->subject('Order Accepted - Order #' . $this->order->id)
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('Great news! Your order has been accepted.')
            ->line('Service: ' . $this->order->service->title)
            ->line('Expected Delivery: ' . $this->order->delivery_date->format('M d, Y'))
            ->action('View Order', route('orders.show', $this->order))
            ->line('The student will deliver your work by the expected delivery date.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id'      => $this->order->id,
            'service_title' => $this->order->service->title,
            'delivery_date' => $this->order->delivery_date,
            'message'       => 'Your order for ' . $this->order->service->title . ' has been accepted',
            'action_url'    => route('orders.show', $this->order),
        ];
    }
}
