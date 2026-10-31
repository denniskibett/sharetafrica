<?php

/*
| Providers a business (or the platform) can connect from the dashboard instead of .env.
| Stored per company in `integrations` (credentials encrypted with APP_KEY); the platform row
| (company_id null) is the fallback for every company. `secret` fields are never sent back to the browser.
| `sandbox`: the provider's published test value, filled in when a connection is first set up, so every
| integration starts in its sandbox (consumer keys still come from the provider's developer portal).
*/

$env = ['type' => 'select', 'options' => ['sandbox' => 'Sandbox (testing)', 'live' => 'Live'], 'required' => true];

return [
    'mpesa' => [
        'name' => 'M-Pesa (Daraja)',
        'category' => 'Payments',
        'description' => 'Safaricom M-Pesa STK push, C2B and B2C payouts through the Daraja API.',
        'color' => '#00A651',
        'fields' => [
            'environment' => ['label' => 'Environment'] + $env,
            'consumer_key' => ['label' => 'Consumer key', 'secret' => true, 'required' => true],
            'consumer_secret' => ['label' => 'Consumer secret', 'secret' => true, 'required' => true],
            'consumer_key' => ['label' => 'Consumer key', 'secret' => true, 'required' => true, 'help' => 'From your app on developer.safaricom.co.ke (sandbox apps are free).'],
            'shortcode' => ['label' => 'Paybill / till number', 'required' => true, 'sandbox' => '174379'],
            'passkey' => ['label' => 'Lipa na M-Pesa passkey', 'secret' => true, 'help' => 'Needed for STK push.', 'sandbox' => 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919'],
            'initiator_name' => ['label' => 'Initiator name', 'help' => 'Needed for B2C payouts.', 'sandbox' => 'testapi'],
            'security_credential' => ['label' => 'Security credential', 'secret' => true, 'help' => 'Needed for B2C payouts.'],
        ],
    ],
    'mtn_momo' => [
        'name' => 'MTN MoMo',
        'category' => 'Payments',
        'description' => 'MTN Mobile Money collections and disbursements (Uganda and other MTN markets).',
        'color' => '#FFCC00',
        'fields' => [
            'environment' => ['label' => 'Environment'] + $env,
            'subscription_key' => ['label' => 'Subscription (primary) key', 'secret' => true, 'required' => true],
            'api_user' => ['label' => 'API user (UUID)', 'required' => true],
            'api_key' => ['label' => 'API key', 'secret' => true, 'required' => true],
            'currency' => ['label' => 'Currency', 'placeholder' => 'UGX', 'sandbox' => 'EUR', 'help' => 'The MTN sandbox only accepts EUR.'],
        ],
    ],
    'airtel_money' => [
        'name' => 'Airtel Money',
        'category' => 'Payments',
        'description' => 'Airtel Money collections and payouts across East Africa.',
        'color' => '#E40000',
        'fields' => [
            'environment' => ['label' => 'Environment'] + $env,
            'client_id' => ['label' => 'Client ID', 'required' => true],
            'client_secret' => ['label' => 'Client secret', 'secret' => true, 'required' => true],
            'country' => ['label' => 'Country code', 'placeholder' => 'KE', 'sandbox' => 'KE'],
            'currency' => ['label' => 'Currency', 'placeholder' => 'KES', 'sandbox' => 'KES'],
        ],
    ],
    'kenyasms' => [
        'name' => 'KenyaSMS',
        'category' => 'Messaging',
        'description' => 'Bulk and transactional SMS in Kenya with your own sender ID.',
        'color' => '#0EA5E9',
        'fields' => [
            'api_url' => ['label' => 'API URL', 'type' => 'url', 'required' => true, 'placeholder' => 'https://'],
            'partner_id' => ['label' => 'Partner ID'],
            'api_key' => ['label' => 'API key', 'secret' => true, 'required' => true],
            'sender_id' => ['label' => 'Sender ID / shortcode', 'required' => true],
        ],
    ],
    'africastalking' => [
        'name' => "Africa's Talking",
        'category' => 'Messaging',
        'description' => 'SMS, USSD and airtime across Africa.',
        'color' => '#F97316',
        'fields' => [
            'username' => ['label' => 'Username', 'required' => true, 'help' => 'Use "sandbox" for testing.', 'sandbox' => 'sandbox'],
            'api_key' => ['label' => 'API key', 'secret' => true, 'required' => true],
            'sender_id' => ['label' => 'Sender ID'],
        ],
    ],
    'smtp' => [
        'name' => 'Email (SMTP)',
        'category' => 'Email',
        'description' => 'Send receipts, thank-you and account emails from your own address.',
        'color' => '#6366F1',
        'fields' => [
            'host' => ['label' => 'SMTP host', 'required' => true, 'placeholder' => 'smtp.example.com'],
            'port' => ['label' => 'Port', 'type' => 'number', 'required' => true, 'placeholder' => '587'],
            'encryption' => ['label' => 'Encryption', 'type' => 'select', 'options' => ['tls' => 'TLS', 'ssl' => 'SSL', '' => 'None']],
            'username' => ['label' => 'Username'],
            'password' => ['label' => 'Password', 'secret' => true],
            'from_address' => ['label' => 'From address', 'type' => 'email', 'required' => true],
            'from_name' => ['label' => 'From name', 'placeholder' => 'Sharet'],
        ],
    ],
    'google' => [
        'name' => 'Google (Maps & Analytics)',
        'category' => 'Website',
        'description' => 'Maps on contact pages and Google Analytics measurement.',
        'color' => '#4285F4',
        'fields' => [
            'maps_key' => ['label' => 'Maps API key', 'secret' => true],
            'analytics_id' => ['label' => 'Analytics measurement ID', 'placeholder' => 'G-XXXXXXX'],
        ],
    ],
];
