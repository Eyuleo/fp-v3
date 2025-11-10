<?php
namespace App\Actions\Orders;

use App\Models\Order;
use App\Notifications\OrderAcceptedNotification;
use Illuminate\Support\Facades\DB;

class AcceptOrderAction
{
    /**
     * Accept an order and transition to in_progress status.
     */
    public function execute(Order $order): Order
    {
        if (! $order->isPending()) {
            throw new \Exception('Only pending orders can be accepted.');
        }

        // Ensure payment has been processed
        if (! $order->payment) {
            throw new \Exception('Cannot accept order: Payment has not been processed yet. Please wait a moment and try again.');
        }

        return DB::transaction(function () use ($order) {
            // Calculate delivery date based on service delivery days
            $deliveryDate = now()->addDays($order->service->delivery_days);

            // Update order status
            $order->update([
                'status'        => Order::STATUS_IN_PROGRESS,
                'delivery_date' => $deliveryDate,
            ]);

            // Send notification to client
            $order->client->notify(new OrderAcceptedNotification($order));

            return $order->fresh();
        });
    }
}
