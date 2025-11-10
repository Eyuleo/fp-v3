<?php
namespace App\Actions\Payments;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class HandleCheckoutSuccessAction
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('stripe.secret'));
    }

    /**
     * Handle successful checkout by retrieving session and creating payment record.
     *
     * @param Order $order
     * @param string $sessionId
     * @return Payment
     * @throws \Exception
     */
    public function execute(Order $order, string $sessionId): Payment
    {
        try {
            // Retrieve the checkout session from Stripe
            $session = $this->stripe->checkout->sessions->retrieve($sessionId, [
                'expand' => ['payment_intent'],
            ]);

            // Verify the session belongs to this order
            if ($session->client_reference_id != $order->id) {
                throw new \Exception('Session does not match order.');
            }

            // Check if payment already exists
            if ($order->payment) {
                Log::info('Payment already exists for order', ['order_id' => $order->id]);
                return $order->payment;
            }

            // Verify payment was successful
            if ($session->payment_status !== 'paid') {
                throw new \Exception('Payment was not successful.');
            }

            return DB::transaction(function () use ($order, $session) {
                // Get payment intent details
                $paymentIntent = $session->payment_intent;
                $chargeId      = $paymentIntent->charges->data[0]->id ?? null;

                // Create payment record
                $payment = Payment::create([
                    'order_id'                 => $order->id,
                    'stripe_payment_intent_id' => $paymentIntent->id,
                    'stripe_charge_id'         => $chargeId,
                    'amount'                   => $order->price,
                    'commission'               => $order->commission,
                    'net_amount'               => $order->net_amount,
                    'status'                   => 'pending', // Will be 'completed' when transferred to student
                    'processed_at'             => now(),
                    'metadata'                 => [
                        'session_id'     => $session->id,
                        'payment_method' => $paymentIntent->payment_method ?? null,
                        'customer_email' => $session->customer_details->email ?? null,
                    ],
                ]);

                Log::info('Payment record created for order', [
                    'order_id'       => $order->id,
                    'payment_id'     => $payment->id,
                    'payment_intent' => $paymentIntent->id,
                    'amount'         => $order->price,
                ]);

                // Send notifications to both parties now that payment is confirmed
                $order->load(['student', 'client', 'service']);

                // Notify student about new order
                $order->student->notify(new \App\Notifications\OrderPlacedNotification($order));

                // Notify client about successful order placement
                $order->client->notify(new \App\Notifications\OrderPlacedNotification($order));

                return $payment;
            });
        } catch (ApiErrorException $e) {
            Log::error('Failed to handle checkout success', [
                'order_id'   => $order->id,
                'session_id' => $sessionId,
                'error'      => $e->getMessage(),
            ]);

            throw new \Exception('Failed to process payment: ' . $e->getMessage());
        }
    }
}
