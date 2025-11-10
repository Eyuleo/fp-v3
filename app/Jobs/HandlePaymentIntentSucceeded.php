<?php
namespace App\Jobs;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class HandlePaymentIntentSucceeded implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries   = 3;
    public $backoff = [60, 120, 300];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public object $paymentIntent
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $payment = Payment::where('stripe_payment_intent_id', $this->paymentIntent->id)->first();

            if (! $payment) {
                Log::warning('Payment not found for payment intent', [
                    'payment_intent_id' => $this->paymentIntent->id,
                ]);
                return;
            }

            // Get the charge ID from the payment intent
            $chargeId = $this->paymentIntent->charges->data[0]->id ?? null;

            // Update payment record
            $payment->update([
                'stripe_charge_id' => $chargeId,
                'status'           => 'pending', // Will be 'completed' after transfer
                'metadata'         => array_merge($payment->metadata ?? [], [
                    'payment_status'  => $this->paymentIntent->status,
                    'amount_received' => $this->paymentIntent->amount_received,
                ]),
            ]);

            Log::info('Payment intent succeeded', [
                'payment_id'        => $payment->id,
                'order_id'          => $payment->order_id,
                'payment_intent_id' => $this->paymentIntent->id,
                'charge_id'         => $chargeId,
            ]);

        } catch (\Exception $e) {
            Log::error('Error handling payment intent succeeded', [
                'payment_intent_id' => $this->paymentIntent->id,
                'error'             => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
