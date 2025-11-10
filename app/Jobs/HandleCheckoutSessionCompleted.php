<?php
namespace App\Jobs;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class HandleCheckoutSessionCompleted implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries   = 3;
    public $backoff = [60, 120, 300]; // Retry after 1, 2, and 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public object $session
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Extract order ID from metadata
            $orderId = $this->session->metadata->order_id ?? $this->session->client_reference_id;

            if (! $orderId) {
                Log::error('No order ID found in checkout session', [
                    'session_id' => $this->session->id,
                ]);
                return;
            }

            $order = Order::find($orderId);

            if (! $order) {
                Log::error('Order not found for checkout session', [
                    'order_id'   => $orderId,
                    'session_id' => $this->session->id,
                ]);
                return;
            }

            // Get payment intent ID
            $paymentIntentId = $this->session->payment_intent;

            // Create or update payment record
            Payment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'stripe_payment_intent_id' => $paymentIntentId,
                    'amount'                   => $order->price,
                    'commission'               => $order->commission,
                    'net_amount'               => $order->net_amount,
                    'status'                   => 'pending',
                    'metadata'                 => [
                        'session_id'     => $this->session->id,
                        'customer_email' => $this->session->customer_email,
                        'payment_status' => $this->session->payment_status,
                    ],
                ]
            );

            Log::info('Checkout session completed', [
                'order_id'          => $order->id,
                'session_id'        => $this->session->id,
                'payment_intent_id' => $paymentIntentId,
            ]);

            // Send notifications
            $order->student->notify(new \App\Notifications\NewOrderNotification($order));
            $order->client->notify(new \App\Notifications\OrderPlacedNotification($order));

        } catch (\Exception $e) {
            Log::error('Error handling checkout session completed', [
                'session_id' => $this->session->id,
                'error'      => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
