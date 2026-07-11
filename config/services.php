<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'tinymce' => [
    'api_key' => env('TINYMCE_API_KEY'),
],

'stripe' => [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
],

'bkash' => [
    'base_url' => env('BKASH_BASE_URL', 'https://checkout.sandbox.bka.sh/v1.2.0-beta'),
    'username' => env('BKASH_USERNAME'),
    'password' => env('BKASH_PASSWORD'),
    'app_key' => env('BKASH_APP_KEY'),
    'app_secret' => env('BKASH_APP_SECRET'),
],

'nagad' => [
    'base_url' => env('NAGAD_BASE_URL', 'https://api.mynagad.com'),
    'merchant_id' => env('NAGAD_MERCHANT_ID'),
    'public_key' => env('NAGAD_PUBLIC_KEY'),
    'private_key' => env('NAGAD_PRIVATE_KEY'),
],

'openai' => [
    'api_key' => env('OPENAI_API_KEY'),
    'organization' => env('OPENAI_ORGANIZATION', null),
],

'elevenlabs' => [
    'api_key' => env('ELEVENLABS_API_KEY'),
    'default_voice_id' => env('ELEVENLABS_DEFAULT_VOICE_ID'),
],

'd_id' => [
    'api_key' => env('DID_API_KEY'),
    'api_url' => env('DID_API_URL', 'https://api.d-id.com'),
],


    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],


    

];
