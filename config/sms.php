<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SMS Configuration
    |--------------------------------------------------------------------------
    */
    
    'default' => env('SMS_DRIVER', 'kenyasms'),
    
    'kenyasms' => [
        'base_url' => env('KENYASMS_URL', 'https://kenyasms.com/api/v1'),
        'api_key' => env('KENYASMS_KEY'),
        'sender_id' => env('KENYASMS_SENDER_ID', 'SHARETENT'),
        'default_type' => env('KENYASMS_DEFAULT_TYPE', 'transactional'),
        'sandbox' => env('KENYASMS_SANDBOX', true),
    ],
    
    'sandbox' => env('SMS_SANDBOX', true),
];
