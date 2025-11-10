<?php
namespace App\Actions\Orders;

use App\Models\Order;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PlaceOrderAction
{
    /**
     * Place a new order for a service.
     *
     * @param Service $service
     * @param User $client
     * @param string $requirements
     * @return array ['order' => Order, 'checkout_url' => string]
     * @throws \Exception
     */
    public function execute(Service $service, User $client, string $requirements): array
    {
        // Validate service is active
        if (! $service->is_active) {
            throw new \Exception('This service is not available for ordering.');
        }

        // Validate client is not the service owner
        if ($service->student_id === $client->id) {
            throw new \Exception('You cannot order your own service.');
        }

        // Calculate commission from config
        $commissionRate = config('stripe.commission_rate', 0.15);
        $commission     = $service->price * $commissionRate;

        // Create order in a transaction
        return DB::transaction(function () use ($service, $client, $requirements, $commission) {
            $order = Order::create([
                'service_id'     => $service->id,
                'student_id'     => $service->student_id,
                'client_id'      => $client->id,
                'price'          => $service->price,
                'commission'     => $commission,
                'requirements'   => $requirements,
                'status'         => Order::STATUS_PENDING,
                'revision_count' => 0,
            ]);

            // Create Stripe Checkout session
            $createCheckoutSession = new \App\Actions\Payments\CreateCheckoutSessionAction();
            $checkoutUrl           = $createCheckoutSession->execute($order);

            // TODO: Fire OrderPlaced event (after payment is confirmed via webhook)
            // event(new OrderPlaced($order));

            return [
                'order'        => $order,
                'checkout_url' => $checkoutUrl,
            ];
        });
    }
}
