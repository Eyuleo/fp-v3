<?php
namespace App\Actions\Orders;

use App\Actions\Payments\RefundOrderAction;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class DeclineOrderAction
{
    /**
     * Decline an order and initiate refund.
     */
    public function execute(Order $order, ?string $reason = null): Order
    {
        if (! $order->isPending()) {
            throw new \Exception('Only pending orders can be declined.');
        }

        return DB::transaction(function () use ($order, $reason) {
            // Update order status
            $order->update([
                'status'           => Order::STATUS_CANCELLED,
                'cancelled_reason' => $reason ?? 'Declined by student',
            ]);

            // Initiate refund
            $refundAction = new RefundOrderAction();
            $refundAction->execute($order);

            // Send notification to client
            $order->client->notify(new \App\Notifications\OrderCancelledNotification($order));

            return $order->fresh();
        });
    }
}
