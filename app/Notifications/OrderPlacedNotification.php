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
        $isStudent = $notifiable->id === $this->order->student_id;

        if ($isStudent) {
            // Notification for student
            return (new MailMessage)
                ->subject('New Order Received - Order #' . $this->order->id)
                ->greeting('Hello ' . $notifiable->first_name . '!')
                ->line('You have received a new order!')
                ->line('**Service:** ' . $this->order->service->title)
                ->line('**Client:** ' . $this->order->client->full_name)
                ->line('**Amount:** $' . number_format($this->order->price, 2))
                ->line('**Your Earnings:** $' . number_format($this->order->net_amount, 2))
                ->action('View Order & Accept', route('orders.show', $this->order))
                ->line('Please review the requirements and accept or decline the order within 48 hours.')
                ->line('Thank you for being part of our platform!');
        } else {
            // Notification for client
            return (new MailMessage)
                ->subject('Order Placed Successfully - Order #' . $this->order->id)
                ->greeting('Hello ' . $notifiable->first_name . '!')
                ->line('Your order has been placed successfully!')
                ->line('**Service:** ' . $this->order->service->title)
                ->line('**Student:** ' . $this->order->student->full_name)
                ->line('**Amount Paid:** $' . number_format($this->order->price, 2))
                ->action('View Order', route('orders.show', $this->order))
                ->line('The student will review your requirements and accept or decline the order within 48 hours.')
                ->line('Thank you for using our platform!');
        }
    }

    public function toArray(object $notifiable): array
    {
        $isStudent = $notifiable->id === $this->order->student_id;

        if ($isStudent) {
            return [
                'title'         => 'New Order Received',
                'message'       => 'You received a new order for ' . $this->order->service->title . ' from ' . $this->order->client->full_name,
                'order_id'      => $this->order->id,
                'service_title' => $this->order->service->title,
                'client_name'   => $this->order->client->full_name,
                'amount'        => $this->order->price,
                'earnings'      => $this->order->net_amount,
                'action_url'    => route('orders.show', $this->order),
            ];
        } else {
            return [
                'title'         => 'Order Placed Successfully',
                'message'       => 'Your order for ' . $this->order->service->title . ' has been placed successfully',
                'order_id'      => $this->order->id,
                'service_title' => $this->order->service->title,
                'student_name'  => $this->order->student->full_name,
                'amount'        => $this->order->price,
                'action_url'    => route('orders.show', $this->order),
            ];
        }
    }
}
