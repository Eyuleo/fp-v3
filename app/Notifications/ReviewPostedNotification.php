<?php
namespace App\Notifications;

use App\Models\Review;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReviewPostedNotification extends Notification
{

    public function __construct(public Review $review)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $stars = str_repeat('⭐', $this->review->rating);

        return (new MailMessage)
            ->subject('New Review Received - ' . $this->review->rating . ' Stars')
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('You have received a new review!')
            ->line('Rating: ' . $stars . ' (' . $this->review->rating . '/5)')
            ->line('Order #' . $this->review->order_id)
            ->when($this->review->text, function ($mail) {
                return $mail->line('Review: "' . \Illuminate\Support\Str::limit($this->review->text, 100) . '"');
            })
            ->action('View Review', route('profile.show'))
            ->line('Thank you for providing excellent service!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'review_id'  => $this->review->id,
            'order_id'   => $this->review->order_id,
            'rating'     => $this->review->rating,
            'text'       => $this->review->text,
            'message'    => 'New ' . $this->review->rating . '-star review received',
            'action_url' => route('profile.show'),
        ];
    }
}
