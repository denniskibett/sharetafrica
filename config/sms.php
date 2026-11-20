<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SMS Configuration
    |--------------------------------------------------------------------------
    */
    
    // Every SMS runs on KenyaSMS with the SHARETENT account (same key as scharge's KENYASMS_KEY). No other provider.
    'default' => env('SMS_DRIVER', 'kenyasms'),
    
    'kenyasms' => [
        'base_url' => env('KENYASMS_URL', 'https://kenyasms.com/api/v1'),
        'api_key' => env('KENYASMS_KEY'),
        'sender_id' => 'SHARETENT', // fixed: Sharet's SMS and one-time codes always go as SHARETENT
        'default_type' => env('KENYASMS_DEFAULT_TYPE', 'transactional'),
        'sandbox' => env('KENYASMS_SANDBOX', true),
    ],
    
    'sandbox' => env('SMS_SANDBOX', true),

    // Every test SMS (Integrations › Send test SMS) goes to this number, never to a customer
    'test_to' => env('SMS_TEST_TO', '0717492048'),
];
