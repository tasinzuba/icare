<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Subscription Configuration
    |--------------------------------------------------------------------------
    */

    'trial_days' => env('SUBSCRIPTION_TRIAL_DAYS', 7),

    'grace_period_days' => env('SUBSCRIPTION_GRACE_PERIOD', 3),

    'auto_renew_default' => env('SUBSCRIPTION_AUTO_RENEW', true),

    'currency' => env('SUBSCRIPTION_CURRENCY', 'BDT'),

    /*
    |--------------------------------------------------------------------------
    | Feature Limits
    |--------------------------------------------------------------------------
    */

    'features' => [
        'free' => [
            'mock_tests_per_month' => 3,
            'ai_evaluations' => false,
            'detailed_analytics' => false,
            'priority_support' => false,
        ],
        'premium' => [
            'mock_tests_per_month' => 'unlimited',
            'ai_evaluations' => true,
            'detailed_analytics' => true,
            'priority_support' => true,
        ],
        'pro' => [
            'mock_tests_per_month' => 'unlimited',
            'ai_evaluations' => true,
            'detailed_analytics' => true,
            'priority_support' => true,
            'tutor_sessions' => 2,
            'certificate' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways
    |--------------------------------------------------------------------------
    */

    'payment_gateways' => [
        'stripe' => [
            'enabled' => env('STRIPE_ENABLED', true),
            'mode' => env('STRIPE_MODE', 'test'),
        ],
        'bkash' => [
            'enabled' => env('BKASH_ENABLED', true),
            'mode' => env('BKASH_MODE', 'sandbox'),
        ],
        'nagad' => [
            'enabled' => env('NAGAD_ENABLED', true),
            'mode' => env('NAGAD_MODE', 'sandbox'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    */

    'notifications' => [
        'expiry_reminder_days' => [7, 3, 1],
        'payment_failed_retry_times' => 3,
        'send_invoice_on_payment' => true,
    ],
];