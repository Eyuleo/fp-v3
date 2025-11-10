<?php
namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PayoutReleasedNotification extends Notification
{

    public function __construct(public Payment $payment)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payout Released - ' . number_format($this->payment->net_amount, 2) . ' ETB')
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('Your payout has been released!')
            ->line('Order #' . $this->payment->order_id)
            ->line('Amount: ' . number_format($this->payment->net_amount, 2) . ' ETB')
            ->line('The funds have been transferred to your Stripe Connect account.')
            ->action('View Order', route('orders.show', $this->payment->order))
            ->line('Thank you for using our platform!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'payment_id' => $this->payment->id,
            'order_id'   => $this->payment->order_id,
            'amount'     => $this->payment->net_amount,
            'message'    => 'Payout of ' . number_format($this->payment->net_amount, 2) . ' ETB released',
            'action_url' => route('orders.show', $this->payment->order),
        ];
    }
}
