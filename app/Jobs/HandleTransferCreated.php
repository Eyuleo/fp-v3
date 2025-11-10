<?php
namespace App\Jobs;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class HandleTransferCreated implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries   = 3;
    public $backoff = [60, 120, 300];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public object $transfer
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Find payment by order_id from metadata
            $orderId = $this->transfer->metadata->order_id ?? null;

            if (! $orderId) {
                Log::warning('No order ID found in transfer metadata', [
                    'transfer_id' => $this->transfer->id,
                ]);
                return;
            }

            $payment = Payment::where('order_id', $orderId)->first();

            if (! $payment) {
                Log::warning('Payment not found for transfer', [
                    'order_id'    => $orderId,
                    'transfer_id' => $this->transfer->id,
                ]);
                return;
            }

            // Update payment with transfer ID
            $payment->update([
                'transfer_id'  => $this->transfer->id,
                'status'       => 'completed',
                'processed_at' => now(),
                'metadata'     => array_merge($payment->metadata ?? [], [
                    'transfer_status' => 'completed',
                    'transferred_at'  => now()->toIso8601String(),
                ]),
            ]);

            Log::info('Transfer created', [
                'payment_id'  => $payment->id,
                'order_id'    => $payment->order_id,
                'transfer_id' => $this->transfer->id,
                'amount'      => $this->transfer->amount,
            ]);

            // TODO: Send notification to student about payout

        } catch (\Exception $e) {
            Log::error('Error handling transfer created', [
                'transfer_id' => $this->transfer->id,
                'error'       => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
