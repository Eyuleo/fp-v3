<?php
namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification
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
            ->subject('Order Placed Successfully - Order #' . $this->order->id)
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('Your order has been placed successfully!')
            ->line('**Service:** ' . $this->order->service->title)
            ->line('**Student:** ' . $this->order->student->full_name)
            ->line('**Amount Paid:** $' . number_format($this->order->price, 2))
            ->line('**Expected Delivery:** ' . ($this->order->delivery_date ? $this->order->delivery_date->format('M d, Y') : 'To be determined'))
            ->action('View Order', route('orders.show', $this->order))
            ->line('The student will review your requirements and accept or decline the order within 48 hours.')
            ->line('Thank you for using our platform!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id'      => $this->order->id,
            'service_title' => $this->order->service->title,
            'student_name'  => $this->order->student->full_name,
            'amount'        => $this->order->price,
            'message'       => 'Order placed successfully for ' . $this->order->service->title,
            'action_url'    => route('orders.show', $this->order),
        ];
    }
}
