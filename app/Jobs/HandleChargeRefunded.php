<?php
namespace App\Jobs;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class HandleChargeRefunded implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries   = 3;
    public $backoff = [60, 120, 300];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public object $charge
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $payment = Payment::where('stripe_charge_id', $this->charge->id)->first();

            if (! $payment) {
                Log::warning('Payment not found for charge', [
                    'charge_id' => $this->charge->id,
                ]);
                return;
            }

            // Update payment status to refunded
            $payment->update([
                'status'       => 'refunded',
                'processed_at' => now(),
                'metadata'     => array_merge($payment->metadata ?? [], [
                    'refund_status' => 'completed',
                    'refunded_at'   => now()->toIso8601String(),
                    'refund_amount' => $this->charge->amount_refunded,
                ]),
            ]);

            Log::info('Charge refunded', [
                'payment_id'      => $payment->id,
                'order_id'        => $payment->order_id,
                'charge_id'       => $this->charge->id,
                'amount_refunded' => $this->charge->amount_refunded,
            ]);

            // TODO: Send notification to client about refund

        } catch (\Exception $e) {
            Log::error('Error handling charge refunded', [
                'charge_id' => $this->charge->id,
                'error'     => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
