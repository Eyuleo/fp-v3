<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    /**
     * Handle incoming Stripe webhooks.
     */
    public function handle(Request $request)
    {
        $payload       = $request->getContent();
        $sigHeader     = $request->header('Stripe-Signature');
        $webhookSecret = config('stripe.webhook_secret');

        try {
            // Verify webhook signature
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            Log::error('Invalid Stripe webhook payload', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (SignatureVerificationException $e) {
            // Invalid signature
            Log::error('Invalid Stripe webhook signature', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Check for idempotency - prevent duplicate processing
        $eventId       = $event->id;
        $existingEvent = DB::table('stripe_events')
            ->where('stripe_event_id', $eventId)
            ->first();

        if ($existingEvent) {
            Log::info('Duplicate Stripe webhook event ignored', ['event_id' => $eventId]);
            return response()->json(['message' => 'Event already processed'], 200);
        }

        // Record the event for idempotency
        DB::table('stripe_events')->insert([
            'stripe_event_id' => $eventId,
            'type'            => $event->type,
            'processed_at'    => now(),
            'created_at'      => now(),
        ]);

        // Dispatch to appropriate handler based on event type
        try {
            match ($event->type) {
                'checkout.session.completed' => $this->handleCheckoutSessionCompleted($event),
                'payment_intent.succeeded'   => $this->handlePaymentIntentSucceeded($event),
                'charge.refunded'            => $this->handleChargeRefunded($event),
                'transfer.created'           => $this->handleTransferCreated($event),
                'account.updated'            => $this->handleAccountUpdated($event),
                default                      => Log::info('Unhandled Stripe webhook event', ['type' => $event->type]),
            };

            return response()->json(['message' => 'Webhook handled'], 200);
        } catch (\Exception $e) {
            Log::error('Error processing Stripe webhook', [
                'event_id'   => $eventId,
                'event_type' => $event->type,
                'error'      => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Handle checkout.session.completed event.
     */
    protected function handleCheckoutSessionCompleted($event)
    {
        dispatch(new \App\Jobs\HandleCheckoutSessionCompleted($event->data->object));
    }

    /**
     * Handle payment_intent.succeeded event.
     */
    protected function handlePaymentIntentSucceeded($event)
    {
        dispatch(new \App\Jobs\HandlePaymentIntentSucceeded($event->data->object));
    }

    /**
     * Handle charge.refunded event.
     */
    protected function handleChargeRefunded($event)
    {
        dispatch(new \App\Jobs\HandleChargeRefunded($event->data->object));
    }

    /**
     * Handle transfer.created event.
     */
    protected function handleTransferCreated($event)
    {
        dispatch(new \App\Jobs\HandleTransferCreated($event->data->object));
    }

    /**
     * Handle account.updated event.
     */
    protected function handleAccountUpdated($event)
    {
        dispatch(new \App\Jobs\HandleAccountUpdated($event->data->object));
    }
}
