<?php
namespace App\Actions\Payments;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class RefundOrderAction
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('stripe.secret'));
    }

    /**
     * Refund an order payment to the client.
     *
     * @param Order $order
     * @param bool $partial Whether this is a partial refund (for disputes)
     * @param float|null $refundAmount The amount to refund (null for full refund)
     * @return Payment
     * @throws \Exception
     */
    public function execute(Order $order, bool $partial = false, ?float $refundAmount = null): Payment
    {
        // Validate order has a payment
        $payment = $order->payment;
        if (! $payment) {
            throw new \Exception('No payment found for this order.');
        }

        // Validate payment can be refunded
        if ($payment->status === 'refunded') {
            throw new \Exception('Payment has already been refunded.');
        }

        if ($payment->status === 'failed') {
            throw new \Exception('Cannot refund a failed payment.');
        }

        try {
            return DB::transaction(function () use ($order, $payment, $partial, $refundAmount) {
                // Calculate refund amount in cents
                $amountToRefund      = $refundAmount ?? $order->price;
                $refundAmountInCents = (int) ($amountToRefund * 100);

                // Create refund parameters
                $refundParams = [
                    'payment_intent' => $payment->stripe_payment_intent_id,
                    'amount'         => $refundAmountInCents,
                    'reason'         => $partial ? 'requested_by_customer' : 'requested_by_customer',
                    'metadata'       => [
                        'order_id'    => $order->id,
                        'refund_type' => $partial ? 'partial' : 'full',
                    ],
                ];

                // If there's a transfer, reverse it
                if ($payment->transfer_id) {
                    $refundParams['reverse_transfer'] = true;
                }

                // If there's an application fee, refund it
                if ($payment->application_fee_id) {
                    $refundParams['refund_application_fee'] = true;
                }

                // Create the refund
                $refund = $this->stripe->refunds->create($refundParams);

                // Update payment record
                $payment->update([
                    'status'       => $partial ? 'completed' : 'refunded',
                    'processed_at' => now(),
                    'metadata'     => array_merge($payment->metadata ?? [], [
                        'refund_id'     => $refund->id,
                        'refund_amount' => $amountToRefund,
                        'refund_type'   => $partial ? 'partial' : 'full',
                        'refunded_at'   => now()->toIso8601String(),
                    ]),
                ]);

                Log::info('Order refunded', [
                    'order_id'  => $order->id,
                    'refund_id' => $refund->id,
                    'amount'    => $amountToRefund,
                    'type'      => $partial ? 'partial' : 'full',
                ]);

                return $payment;
            });
        } catch (ApiErrorException $e) {
            Log::error('Failed to refund order', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);

            throw new \Exception('Failed to process refund: ' . $e->getMessage());
        }
    }
}
