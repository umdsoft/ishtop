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

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // IshTop services
    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'bot_username' => env('TELEGRAM_BOT_USERNAME'),
        'webhook_url' => env('TELEGRAM_WEBHOOK_URL'),
    ],

    'payme' => [
        'merchant_id' => env('PAYME_MERCHANT_ID'),
        'secret_key' => env('PAYME_SECRET_KEY'),
        'test_key' => env('PAYME_TEST_KEY'),
    ],

    'click' => [
        'merchant_id' => env('CLICK_MERCHANT_ID'),
        'service_id' => env('CLICK_SERVICE_ID'),
        'secret_key' => env('CLICK_SECRET_KEY'),
    ],

    'uzum' => [
        'merchant_id' => env('UZUM_MERCHANT_ID'),
        'secret_key' => env('UZUM_SECRET_KEY'),
    ],

    'claude' => [
        'key' => env('ANTHROPIC_API_KEY'),
    ],

    'deepseek' => [
        'key' => env('DEEPSEEK_API_KEY'),
    ],

    'sms' => [
        'email' => env('SMS_ESKIZ_EMAIL'),
        'password' => env('SMS_ESKIZ_PASSWORD'),
        'from' => env('SMS_FROM', '4546'),
    ],

    'yandex_maps' => [
        'key' => env('YANDEX_MAPS_API_KEY'),
    ],

];
