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

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    
    'google' => [
        'analytics_id' => env('GOOGLE_ANALYTICS_ID'),
    ],

    'kenyasms' => [
        'key' => env('KENYASMS_KEY'),
        'url' => env('KENYASMS_URL', 'https://kenyasms.com/api/v1'),
        'sender_id' => env('KENYASMS_SENDER_ID'),
        'default_type' => env('KENYASMS_DEFAULT_TYPE', 'transactional'),
        'sandbox' => env('KENYASMS_SANDBOX', false),
    ],

    // Reads ID photos at verification (App\Integrations\Contracts\IdReader). "none" where tesseract isn't installed.
    'tesseract' => [
        'reader' => env('ID_READER', 'tesseract'),
        'path' => env('TESSERACT_PATH', 'tesseract'),
    ],

    // First-party cross-site analytics (page views + conversions). Loaded on public pages in production only.
    'sharet_analytics' => [
        'url' => env('SHARET_ANALYTICS_URL', 'https://sharet.africa'),
        'site' => 'sharet',
    ],

];
