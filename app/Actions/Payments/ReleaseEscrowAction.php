<?php
namespace App\Actions\Payments;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class ReleaseEscrowAction
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('stripe.secret'));
    }

    /**
     * Release escrow funds to the student upon order completion.
     *
     * @param Order $order
     * @return Payment
     * @throws \Exception
     */
    public function execute(Order $order): Payment
    {
        // Validate order has a payment
        $payment = $order->payment;
        if (! $payment) {
            throw new \Exception('No payment found for this order.');
        }

        // Validate payment is in pending or completed status
        if (! in_array($payment->status, ['pending', 'completed'])) {
            throw new \Exception('Payment cannot be released in current status: ' . $payment->status);
        }

        // If already completed, return the payment
        if ($payment->status === 'completed' && $payment->transfer_id) {
            return $payment;
        }

        // Validate student has Stripe Connect account
        $student = $order->student;
        if (! $student->stripe_connect_account_id) {
            throw new \Exception('Student does not have a Stripe Connect account.');
        }

        try {
            return DB::transaction(function () use ($order, $payment, $student) {
                // Calculate amounts in cents
                $amountInCents     = (int) ($order->price * 100);
                $commissionInCents = (int) ($order->commission * 100);
                $netAmountInCents  = $amountInCents - $commissionInCents;

                // Create transfer to student's Connect account
                $transfer = $this->stripe->transfers->create([
                    'amount'             => $netAmountInCents,
                    'currency'           => strtolower(config('stripe.currency')),
                    'destination'        => $student->stripe_connect_account_id,
                    'description'        => 'Payout for Order #' . $order->id,
                    'metadata'           => [
                        'order_id'   => $order->id,
                        'student_id' => $student->id,
                        'commission' => $commissionInCents,
                    ],
                    'source_transaction' => $payment->stripe_charge_id,
                ]);

                // Update payment record
                $payment->update([
                    'transfer_id'  => $transfer->id,
                    'status'       => 'completed',
                    'processed_at' => now(),
                ]);

                Log::info('Escrow released for order', [
                    'order_id'    => $order->id,
                    'transfer_id' => $transfer->id,
                    'amount'      => $order->price,
                    'commission'  => $order->commission,
                    'net_amount'  => $order->net_amount,
                ]);

                return $payment;
            });
        } catch (ApiErrorException $e) {
            Log::error('Failed to release escrow', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);

            throw new \Exception('Failed to release payment: ' . $e->getMessage());
        }
    }
}
