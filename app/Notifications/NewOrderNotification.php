<?php
namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification
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
            ->subject('New Order Received - Order #' . $this->order->id)
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('You have received a new order for your service!')
            ->line('**Service:** ' . $this->order->service->title)
            ->line('**Client:** ' . $this->order->client->full_name)
            ->line('**Price:** $' . number_format($this->order->price, 2))
            ->line('**Your Earnings:** $' . number_format($this->order->net_amount, 2))
            ->line('**Delivery Date:** ' . ($this->order->delivery_date ? $this->order->delivery_date->format('M d, Y') : 'Not set'))
            ->action('View Order', route('orders.show', $this->order))
            ->line('Please review the order requirements and accept or decline the order.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id'     => $this->order->id,
            'service_id'   => $this->order->service_id,
            'service_name' => $this->order->service->title,
            'client_name'  => $this->order->client->full_name,
            'price'        => $this->order->price,
            'message'      => 'New order received for ' . $this->order->service->title,
            'action_url'   => route('orders.show', $this->order),
        ];
    }
}
