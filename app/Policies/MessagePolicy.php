<?php
namespace App\Policies;

use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    /**
     * Determine whether the user can view the model.
     * User can view if they are sender, receiver, or admin
     */
    public function view(User $user, Message $message): bool
    {
        return $user->isAdmin()
        || $user->id === $message->sender_id
        || $user->id === $message->receiver_id;
    }

    /**
     * Determine whether the user can create messages.
     * User can send messages if they are order participants or sending pre-order inquiry
     */
    public function store(User $user, ?int $orderId = null, ?int $serviceId = null): bool
    {
        // For order messages, user must be a participant
        if ($orderId) {
            $order = \App\Models\Order::find($orderId);
            if (! $order) {
                return false;
            }
            return $user->id === $order->client_id || $user->id === $order->student_id;
        }

        // For pre-order inquiry messages, any authenticated user can send
        if ($serviceId) {
            return true;
        }

        return false;
    }
}
