<?php
namespace App\Actions\Orders;

use App\Models\Order;
use App\Notifications\RevisionRequestedNotification;
use Illuminate\Support\Facades\DB;

class RequestRevisionAction
{
    /**
     * Request a revision on delivered work.
     */
    public function execute(Order $order, string $feedback): Order
    {
        if (! $order->isDelivered()) {
            throw new \Exception('Only delivered orders can have revisions requested.');
        }

        if (! $order->canRequestRevision()) {
            throw new \Exception('Maximum number of revisions (' . Order::MAX_REVISIONS . ') has been reached.');
        }

        return DB::transaction(function () use ($order, $feedback) {
            // Update order status and increment revision count
            $order->update([
                'status'         => Order::STATUS_REVISION_REQUESTED,
                'revision_count' => $order->revision_count + 1,
            ]);

            // Create a message with the revision feedback
            $order->messages()->create([
                'sender_id'   => $order->client_id,
                'receiver_id' => $order->student_id,
                'content'     => "Revision requested:\n\n" . $feedback,
                'is_read'     => false,
            ]);

            // Send notification to student
            $order->student->notify(new RevisionRequestedNotification($order, $feedback));

            return $order->fresh();
        });
    }
}
