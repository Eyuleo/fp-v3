<?php
namespace App\Notifications;

use App\Models\Message;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MessageReceivedNotification extends Notification
{

    public function __construct(public Message $message)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('New Message from ' . $this->message->sender->full_name)
            ->greeting('Hello ' . $notifiable->first_name . '!')
            ->line('You have received a new message from ' . $this->message->sender->full_name);

        if ($this->message->order_id) {
            $mail->line('Regarding Order #' . $this->message->order_id);
        }

        $mail->line('Message preview: ' . \Illuminate\Support\Str::limit($this->message->content, 100))
            ->action('View Message', route('messages.show', [
                'user_id'    => $this->message->sender_id,
                'order_id'   => $this->message->order_id,
                'service_id' => $this->message->service_id,
            ]));

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message_id'  => $this->message->id,
            'sender_name' => $this->message->sender->full_name,
            'order_id'    => $this->message->order_id,
            'service_id'  => $this->message->service_id,
            'preview'     => \Illuminate\Support\Str::limit($this->message->content, 100),
            'message'     => 'New message from ' . $this->message->sender->full_name,
            'action_url'  => route('messages.show', [
                'user_id'    => $this->message->sender_id,
                'order_id'   => $this->message->order_id,
                'service_id' => $this->message->service_id,
            ]),
        ];
    }
}
