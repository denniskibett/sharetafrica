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
    // M-Pesa has two connections. Only one is on at a time (same `group`); when the live one is on and
    // filled in it is the one used (App\Services\Integrations::mpesa()).
    'mpesa' => [
        'name' => 'M-Pesa · Live',
        'category' => 'Payments',
        'group' => 'mpesa',
        'environment' => 'live',
        'description' => 'Your production Daraja app: real STK push, C2B and B2C payouts. Takes over from the sandbox when it is on.',
        'color' => '#00A651',
        'logo' => 'images/integrations/mpesa.svg',
        'fields' => [
            'consumer_key' => ['label' => 'Consumer key', 'secret' => true, 'required' => true, 'help' => 'From your production app on developer.safaricom.co.ke (Go Live).'],
            'consumer_secret' => ['label' => 'Consumer secret', 'secret' => true, 'required' => true],
            'shortcode' => ['label' => 'Paybill / till number', 'required' => true, 'help' => 'For a till: the store (head office) number here and the till below.'],
            'kind' => ['label' => 'Shortcode type', 'type' => 'select', 'options' => ['paybill' => 'Paybill', 'till' => 'Till (Buy Goods)']],
            'party_b' => ['label' => 'Till number', 'help' => 'Only for a till whose number differs from the store number.'],
            'passkey' => ['label' => 'Lipa na M-Pesa passkey', 'secret' => true, 'help' => 'Safaricom emails it after Go Live. Needed for STK push.'],
            'initiator_name' => ['label' => 'Initiator name', 'help' => 'Needed for B2C payouts.'],
            'security_credential' => ['label' => 'Security credential', 'secret' => true, 'help' => 'Needed for B2C payouts.'],
        ],
    ],
    'mpesa_sandbox' => [
        'name' => 'M-Pesa · Sandbox',
        'category' => 'Payments',
        'group' => 'mpesa',
        'environment' => 'sandbox',
        'description' => 'Safaricom test environment: try STK push and payouts without moving real money. Comes with the public test paybill.',
        'color' => '#00A651',
        'logo' => 'images/integrations/mpesa.svg',
        'fields' => [
            'consumer_key' => ['label' => 'Consumer key', 'secret' => true, 'required' => true, 'help' => 'From a sandbox app on developer.safaricom.co.ke (free).'],
            'consumer_secret' => ['label' => 'Consumer secret', 'secret' => true, 'required' => true],
            'shortcode' => ['label' => 'Test paybill', 'required' => true, 'sandbox' => '174379'],
            'passkey' => ['label' => 'Test passkey', 'secret' => true, 'sandbox' => 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919'],
            'initiator_name' => ['label' => 'Test initiator', 'sandbox' => 'testapi'],
            'security_credential' => ['label' => 'Security credential', 'secret' => true, 'help' => 'Generate it on the portal with the sandbox certificate.'],
        ],
    ],
    'mtn_momo' => [
        'name' => 'MTN MoMo',
        'category' => 'Payments',
        'description' => 'MTN Mobile Money collections and disbursements (Uganda and other MTN markets).',
        'color' => '#FFCC00',
        'logo' => 'images/integrations/mtn.svg',
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
        'logo' => 'images/integrations/airtel.svg',
        'fields' => [
            'environment' => ['label' => 'Environment'] + $env,
            'client_id' => ['label' => 'Client ID', 'required' => true],
            'client_secret' => ['label' => 'Client secret', 'secret' => true, 'required' => true],
            'country' => ['label' => 'Country code', 'placeholder' => 'KE', 'sandbox' => 'KE'],
            'currency' => ['label' => 'Currency', 'placeholder' => 'KES', 'sandbox' => 'KES'],
        ],
    ],
    'vodacom_tz' => [
        'name' => 'M-Pesa Tanzania (Vodacom)',
        'category' => 'Payments',
        'description' => 'Vodacom M-Pesa Tanzania through its OpenAPI: collections (C2B) and payouts (B2C) in TZS.',
        'color' => '#E60000',
        'logo' => 'images/integrations/vodacom.svg',
        'fields' => [
            'environment' => ['label' => 'Environment'] + $env,
            'api_key' => ['label' => 'API key', 'secret' => true, 'required' => true, 'help' => 'From openapiportal.m-pesa.com (your Vodacom Tanzania application).'],
            'public_key' => ['label' => 'Public key', 'secret' => true, 'required' => true, 'help' => 'Used to encrypt the API key into a session key.'],
            'service_provider_code' => ['label' => 'Service provider code', 'required' => true, 'placeholder' => '000000'],
            'currency' => ['label' => 'Currency', 'placeholder' => 'TZS', 'sandbox' => 'TZS'],
        ],
    ],
    'kenyasms' => [
        'name' => 'KenyaSMS',
        'category' => 'Messaging',
        'description' => 'Bulk and transactional SMS in Kenya with your own sender ID.',
        'color' => '#0EA5E9',
        'logo' => 'images/integrations/kenyasms.png',
        'fields' => [
            'api_url' => ['label' => 'API URL', 'type' => 'url', 'required' => true, 'placeholder' => 'https://kenyasms.com/api/v1', 'sandbox' => 'https://kenyasms.com/api/v1'],
            'api_key' => ['label' => 'API key', 'secret' => true, 'required' => true, 'help' => 'Developers → API Keys on kenyasms.com (ks_live_…). Needs the sms.send scope.'],
            'sender_id' => ['label' => 'Sender ID', 'required' => true, 'placeholder' => 'SHARET', 'help' => 'Your approved sender name, up to 11 characters.'],
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
        'logo' => 'images/integrations/email.svg',
        'fields' => [
            'host' => ['label' => 'SMTP host', 'required' => true, 'placeholder' => 'sharet.africa', 'help' => 'Our CyberPanel mail server: sharet.africa, port 587, TLS, with the full mailbox address as the username.'],
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
        'logo' => 'images/integrations/google.svg',
        'fields' => [
            'maps_key' => ['label' => 'Maps API key', 'secret' => true],
            'analytics_id' => ['label' => 'Analytics measurement ID', 'placeholder' => 'G-XXXXXXX'],
        ],
    ],
];
