<?php

/*
| Growth hub (Administration › Growth): the seven Sharet-family sites that send page views and conversions to
| sharet.africa's first-party collector (public/js/sharet-analytics.js → POST /t/x), their SEO audit and the
| Ad center. Keys are fixed: they are what each site puts in data-site="…".
| sharet_id / company_id: the Sharet business that owns the site (by Sharet ID, which is the same on every database; company_id only if set in .env). That
| business's API keys may read the site's numbers through GET /api/v1/growth/summary (the localized Growth tab in
| each app). Both null = no business linked yet. Scharge's SH-QPAKAK is its Sharet business wallet (pay-link payee).
*/
return [
    'sites' => [
        'sharet' => [
            'company_id' => env('GROWTH_SHARET_COMPANY_ID'),
            'sharet_id' => env('GROWTH_SHARET_SHARET_ID'),
            'name' => 'Sharet',
            'url' => 'https://sharet.africa',
            'origins' => ['https://sharet.africa', 'https://www.sharet.africa'],
            'product' => 'Payments API for Kenya',
            'color' => '#DC2626',
        ],
        'breve' => [
            'company_id' => env('GROWTH_BREVE_COMPANY_ID'),
            'sharet_id' => env('GROWTH_BREVE_SHARET_ID', 'SH-WNY7WK'),
            'name' => 'Breve',
            'url' => 'https://breve.sharet.africa',
            'origins' => ['https://breve.sharet.africa'],
            'product' => 'Invoicing & M-Pesa payments for SMEs',
            'color' => '#2563EB',
        ],
        'aiyotickets' => [
            'company_id' => env('GROWTH_AIYOTICKETS_COMPANY_ID'),
            'sharet_id' => env('GROWTH_AIYOTICKETS_SHARET_ID', 'SH-TBBKGK'),
            'name' => 'aiyotickets',
            'url' => 'https://aiyotickets.com',
            'origins' => ['https://aiyotickets.com', 'https://www.aiyotickets.com'],
            'product' => 'Events & tickets in Nairobi',
            'color' => '#9333EA',
        ],
        'eserviz' => [
            'company_id' => env('GROWTH_ESERVIZ_COMPANY_ID'),
            'sharet_id' => env('GROWTH_ESERVIZ_SHARET_ID', 'SH-G2AR85'),
            'name' => 'eServiz',
            'url' => 'https://eserviz.aiyotickets.com',
            'origins' => ['https://eserviz.aiyotickets.com'],
            'product' => 'Government errands in Kenya (NTSA, Ardhi, KRA, good conduct…)',
            'color' => '#059669',
        ],
        'scharge' => [
            'company_id' => env('GROWTH_SCHARGE_COMPANY_ID'),
            'sharet_id' => env('GROWTH_SCHARGE_SHARET_ID', 'SH-QPAKAK'),
            'name' => 'Scharge',
            'url' => 'https://scharge.sharet.africa',
            'origins' => ['https://scharge.sharet.africa'],
            'product' => 'Property & rent management',
            'color' => '#64748B',
        ],
        'loans' => [
            'company_id' => env('GROWTH_LOANS_COMPANY_ID'),
            'sharet_id' => env('GROWTH_LOANS_SHARET_ID', 'SH-EPQRQ8'),
            'name' => 'Sharet Loans',
            'url' => 'https://loans.sharet.africa',
            'origins' => ['https://loans.sharet.africa'],
            'product' => 'Boda boda & e-bike loans',
            'color' => '#D97706',
        ],
        'shoope' => [
            'company_id' => env('GROWTH_SHOOPE_COMPANY_ID'),
            'sharet_id' => env('GROWTH_SHOOPE_SHARET_ID', 'SH-EKKTQA'),
            'name' => 'Shoope',
            'url' => 'https://shoope.africa',
            'origins' => ['https://shoope.africa', 'https://www.shoope.africa'],
            'product' => 'Safe payments for online shops',
            'color' => '#0EA5E9',
        ],
    ],

    // Extra origins allowed to post as a site, e.g. "sharet=http://127.0.0.1:8000,breve=http://breve.test"
    // (local testing only; leave empty on live).
    'extra_origins' => env('SHARET_ANALYTICS_EXTRA_ORIGINS', ''),

    // Conversion names the collector accepts (window.sa.push(['conversion', '<name>'])), with report labels.
    // The first seven are the cross-site contract; the rest are obvious site-specific extras.
    'conversions' => [
        'signup' => 'Sign-up',
        'lead' => 'Lead (contact / demo / quote)',
        'purchase' => 'Purchase (ticket order paid)',
        'booking' => 'Service booking',
        'loan_application' => 'Loan application',
        'api_keys' => 'API keys created',
        'invoice_created' => 'First invoice created',
        'demo' => 'Book a demo',
        'waitlist' => 'Waitlist',
        'contact' => 'Contact form',
        'quote' => 'Quote request',
        'subscribe' => 'Newsletter / alerts',
        'order' => 'Order placed',
        'event_created' => 'Event created (organiser)',
        'organiser_signup' => 'Organiser sign-up',
        'provider_signup' => 'Service provider sign-up',
        'listing_request' => 'Listing viewing / booking request',
        'listing_alert' => 'Listing SMS alert',
        'host_register' => 'BnB host sign-up',
    ],

    // Ad center thresholds: below these the hub says "not enough data" instead of guessing.
    'min_visitors' => (int) env('GROWTH_MIN_VISITORS', 100),
    'min_conversions' => (int) env('GROWTH_MIN_CONVERSIONS', 10),

    // SEO audit (php artisan growth:seo-audit): sitemap URLs sampled per site, and the HTTP timeout.
    'audit_sample' => 5,
    'audit_timeout' => 15,
];
