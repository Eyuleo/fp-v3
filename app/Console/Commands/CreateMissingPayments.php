<?php
namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Console\Command;

class CreateMissingPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:create-missing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create payment records for orders that are missing them (development/testing)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for orders without payment records...');

        $ordersWithoutPayments = Order::whereDoesntHave('payment')
            ->where('status', '!=', Order::STATUS_CANCELLED)
            ->get();

        if ($ordersWithoutPayments->isEmpty()) {
            $this->info('No orders found without payment records.');
            return 0;
        }

        $this->info("Found {$ordersWithoutPayments->count()} orders without payment records.");

        foreach ($ordersWithoutPayments as $order) {
            Payment::create([
                'order_id'                 => $order->id,
                'stripe_payment_intent_id' => 'pi_test_' . $order->id, // Test payment intent ID
                'stripe_charge_id'         => 'ch_test_' . $order->id, // Test charge ID
                'amount'                   => $order->price,
                'commission'               => $order->commission,
                'net_amount'               => $order->net_amount,
                'status'                   => 'pending',
                'metadata'                 => [
                    'note'     => 'Created manually for testing',
                    'order_id' => $order->id,
                ],
            ]);

            $this->line("✓ Created payment record for Order #{$order->id}");
        }

        $this->info("\nSuccessfully created {$ordersWithoutPayments->count()} payment records.");
        return 0;
    }
}
