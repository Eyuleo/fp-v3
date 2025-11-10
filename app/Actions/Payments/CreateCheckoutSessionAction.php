<?php
namespace App\Actions\Payments;

use App\Models\Order;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class CreateCheckoutSessionAction
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('stripe.secret'));
    }

    /**
     * Create a Stripe Checkout session for an order.
     *
     * @param Order $order
     * @return string The checkout session URL
     * @throws ApiErrorException
     */
    public function execute(Order $order): string
    {
        // Calculate amounts in cents (Stripe uses smallest currency unit)
        $amountInCents = (int) ($order->price * 100);

        // Create checkout session
        $session = $this->stripe->checkout->sessions->create([
            'payment_method_types' => ['card'],
            'line_items'           => [
                [
                    'price_data' => [
                        'currency'     => strtolower(config('stripe.currency')),
                        'product_data' => [
                            'name'        => $order->service->title,
                            'description' => 'Order #' . $order->id . ' - ' . substr($order->requirements, 0, 100),
                        ],
                        'unit_amount'  => $amountInCents,
                    ],
                    'quantity'   => 1,
                ],
            ],
            'mode'                 => 'payment',
            'success_url'          => route('orders.show', $order) . '?payment=success',
            'cancel_url'           => route('orders.show', $order) . '?payment=cancelled',
            'metadata'             => [
                'order_id'   => $order->id,
                'student_id' => $order->student_id,
                'client_id'  => $order->client_id,
            ],
            'client_reference_id'  => (string) $order->id,
        ]);

        return $session->url;
    }
}
