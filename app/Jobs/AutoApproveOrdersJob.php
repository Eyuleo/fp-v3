<?php
namespace App\Jobs;

use App\Actions\Orders\ApproveOrderAction;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AutoApproveOrdersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(ApproveOrderAction $approveOrderAction): void
    {
        // Find delivered orders that are older than 5 days
        $fiveDaysAgo = Carbon::now()->subDays(5);

        $orders = Order::where('status', Order::STATUS_DELIVERED)
            ->where('updated_at', '<=', $fiveDaysAgo)
            ->get();

        Log::info('AutoApproveOrdersJob: Found ' . $orders->count() . ' orders to auto-approve');

        foreach ($orders as $order) {
            try {
                $approveOrderAction->execute($order);
                Log::info('AutoApproveOrdersJob: Auto-approved order #' . $order->id);
            } catch (\Exception $e) {
                Log::error('AutoApproveOrdersJob: Failed to auto-approve order #' . $order->id . ': ' . $e->getMessage());
            }
        }
    }
}
