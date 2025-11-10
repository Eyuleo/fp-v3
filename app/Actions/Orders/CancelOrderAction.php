<?php
namespace App\Actions\Orders;

use App\Actions\Payments\RefundOrderAction;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class CancelOrderAction
{
    /**
     * Cancel an order based on current state.
     */
    public function execute(Order $order, string $reason, int $cancelledBy): Order
    {
        // Validate cancellation is allowed
        if ($order->isCompleted()) {
            throw new \Exception('Completed orders cannot be cancelled.');
        }

        if ($order->isCancelled()) {
            throw new \Exception('Order is already cancelled.');
        }

        return DB::transaction(function () use ($order, $reason, $cancelledBy) {
            // Determine if refund is needed based on order state
            $needsRefund = $order->isPending() || $order->isInProgress() || $order->isRevisionRequested();

            // Update order status
            $order->update([
                'status'           => Order::STATUS_CANCELLED,
                'cancelled_reason' => $reason,
            ]);

            // Process refund if payment was made
            if ($needsRefund && $order->payment) {
                $refundAction = new RefundOrderAction();
                $refundAction->execute($order);
            }

            // Send notification to the other party
            $notifyUser = $cancelledBy === $order->client_id ? $order->student : $order->client;
            $notifyUser->notify(new \App\Notifications\OrderCancelledNotification($order));

            return $order->fresh();
        });
    }
}
