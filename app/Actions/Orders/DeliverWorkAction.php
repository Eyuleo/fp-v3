<?php
namespace App\Actions\Orders;

use App\Models\Order;
use App\Notifications\WorkDeliveredNotification;
use Illuminate\Support\Facades\DB;

class DeliverWorkAction
{
    /**
     * Mark work as delivered and transition to delivered status.
     */
    public function execute(Order $order, ?string $deliveryNote = null, ?array $files = null): Order
    {
        if (! $order->isInProgress() && ! $order->isRevisionRequested()) {
            throw new \Exception('Only in-progress or revision-requested orders can be delivered.');
        }

        return DB::transaction(function () use ($order, $deliveryNote, $files) {
            // Update order status
            $order->update([
                'status' => Order::STATUS_DELIVERED,
            ]);

            // Create delivery message with note
            $message = $order->messages()->create([
                'sender_id'   => $order->student_id,
                'receiver_id' => $order->client_id,
                'content'     => $deliveryNote ?? 'Work has been delivered.',
                'is_read'     => false,
            ]);

            // Store delivery files if provided
            if ($files && count($files) > 0) {
                // Attach first file to the main delivery message
                $firstFile = $files[0];
                $path      = $firstFile->store('order-deliveries', 'private');
                $message->update(['attachment_path' => $path]);

                // Create additional messages for remaining files
                for ($i = 1; $i < count($files); $i++) {
                    $file = $files[$i];
                    $path = $file->store('order-deliveries', 'private');

                    $order->messages()->create([
                        'sender_id'       => $order->student_id,
                        'receiver_id'     => $order->client_id,
                        'content'         => 'Delivery file: ' . $file->getClientOriginalName(),
                        'attachment_path' => $path,
                        'is_read'         => false,
                    ]);
                }
            }

            // Send notification to client
            $order->client->notify(new WorkDeliveredNotification($order));

            return $order->fresh();
        });
    }
}
