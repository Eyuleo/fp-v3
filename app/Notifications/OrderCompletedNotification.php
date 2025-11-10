<?php
namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCompletedNotification extends Notification
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
        $mail = (new MailMessage)
            ->subject('Order Completed - Order #' . $this->order->id)
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('Order #' . $this->order->id . ' has been completed.')
            ->line('Service: ' . $this->order->service->title);

        if ($notifiable->isClient()) {
            $mail->line('Thank you for your order!')
                ->action('Leave a Review', route('orders.show', $this->order))
                ->line('We\'d love to hear about your experience. Please consider leaving a review.');
        } else {
            $mail->line('Payment has been released to your account.')
                ->action('View Order', route('orders.show', $this->order));
        }

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id'      => $this->order->id,
            'service_title' => $this->order->service->title,
            'message'       => 'Order #' . $this->order->id . ' completed',
            'action_url'    => route('orders.show', $this->order),
        ];
    }
}
