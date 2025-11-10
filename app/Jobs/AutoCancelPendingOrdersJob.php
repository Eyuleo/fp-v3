<?php
namespace App\Jobs;

use App\Actions\Orders\DeclineOrderAction;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AutoCancelPendingOrdersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(DeclineOrderAction $declineOrderAction): void
    {
        // Find pending orders that are older than 48 hours
        $fortyEightHoursAgo = Carbon::now()->subHours(48);

        $orders = Order::where('status', Order::STATUS_PENDING)
            ->where('created_at', '<=', $fortyEightHoursAgo)
            ->get();

        Log::info('AutoCancelPendingOrdersJob: Found ' . $orders->count() . ' orders to auto-cancel');

        foreach ($orders as $order) {
            try {
                $declineOrderAction->execute($order, 'Order automatically cancelled due to no response from student within 48 hours');
                Log::info('AutoCancelPendingOrdersJob: Auto-cancelled order #' . $order->id);
            } catch (\Exception $e) {
                Log::error('AutoCancelPendingOrdersJob: Failed to auto-cancel order #' . $order->id . ': ' . $e->getMessage());
            }
        }
    }
}
