<?php
namespace App\Actions\Orders;

use App\Actions\Payments\ReleaseEscrowAction;
use App\Models\Order;
use App\Notifications\OrderCompletedNotification;
use Illuminate\Support\Facades\DB;

class ApproveOrderAction
{
    /**
     * Approve delivered work and complete the order.
     */
    public function execute(Order $order): Order
    {
        if (! $order->isDelivered()) {
            throw new \Exception('Only delivered orders can be approved.');
        }

        return DB::transaction(function () use ($order) {
            // Update order status
            $order->update([
                'status' => Order::STATUS_COMPLETED,
            ]);

            // Try to release escrow payment to student
            try {
                $releaseEscrowAction = new ReleaseEscrowAction();
                $releaseEscrowAction->execute($order);
            } catch (\Exception $e) {
                // Log the error but don't fail the order completion
                // This allows manual processing if needed
                \Log::error('Failed to release escrow for order', [
                    'order_id' => $order->id,
                    'error'    => $e->getMessage(),
                ]);

                // Re-throw if it's a critical error (no payment record)
                if (str_contains($e->getMessage(), 'No payment found')) {
                    throw new \Exception('Cannot complete order: ' . $e->getMessage() . ' Please contact support.');
                }
            }

            // Send notifications to both parties
            $order->client->notify(new OrderCompletedNotification($order));
            $order->student->notify(new OrderCompletedNotification($order));

            return $order->fresh();
        });
    }
}
