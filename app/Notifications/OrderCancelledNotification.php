<?php
namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCancelledNotification extends Notification
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
            ->subject('Order Cancelled - Order #' . $this->order->id)
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('Order #' . $this->order->id . ' has been cancelled.')
            ->line('**Service:** ' . $this->order->service->title)
            ->line('**Reason:** ' . $this->order->cancelled_reason)
            ->when($notifiable->isClient(), function ($mail) {
                return $mail->line('A full refund will be processed to your original payment method within 5-10 business days.');
            })
            ->action('View Order', route('orders.show', $this->order))
            ->line('If you have any questions, please contact support.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id'      => $this->order->id,
            'service_title' => $this->order->service->title,
            'reason'        => $this->order->cancelled_reason,
            'message'       => 'Order #' . $this->order->id . ' has been cancelled',
            'action_url'    => route('orders.show', $this->order),
        ];
    }
}
