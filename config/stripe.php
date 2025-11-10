<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Stripe API Keys
    |--------------------------------------------------------------------------
    |
    | The Stripe publishable and secret keys for your application.
    | Use test mode keys for development and live mode keys for production.
    |
    */

    'key'             => env('STRIPE_KEY'),

    'secret'          => env('STRIPE_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Stripe Webhook Secret
    |--------------------------------------------------------------------------
    |
    | The webhook signing secret used to verify webhook signatures from Stripe.
    | This ensures that webhook events are genuinely from Stripe.
    |
    */

    'webhook_secret'  => env('STRIPE_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Stripe API Version
    |--------------------------------------------------------------------------
    |
    | The Stripe API version to use. This should match the version you're
    | developing against to ensure consistent behavior.
    |
    */

    'api_version'     => '2024-11-20.acacia',

    /*
    |--------------------------------------------------------------------------
    | Platform Commission Rate
    |--------------------------------------------------------------------------
    |
    | The percentage of each transaction that the platform takes as commission.
    | This is used to calculate application fees on Stripe Connect transfers.
    | Value should be between 0 and 1 (e.g., 0.15 for 15%).
    |
    */

    'commission_rate' => env('STRIPE_COMMISSION_RATE', 0.15),

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | The default currency for all transactions on the platform.
    | Ethiopian Birr (ETB) is used for the Student Marketplace.
    |
    */

    'currency'        => env('STRIPE_CURRENCY', 'ETB'),

    /*
    |--------------------------------------------------------------------------
    | Connect Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for Stripe Connect Express accounts used by students
    | to receive payouts.
    |
    */

    'connect'         => [
        'account_type' => 'express',
        'capabilities' => [
            'card_payments',
            'transfers',
        ],
    ],

];
