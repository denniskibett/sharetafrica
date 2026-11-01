<?php

/*
| Payment rails (docs/v2/RAILS.md). Every external payment API is an adapter behind App\Integrations\Contracts\PaymentRail;
| credentials come from the encrypted integrations rows (App\Services\Integrations), never from code.
|
| Until Cashia and Daraja are integrated, `simulator` stands in for them: it behaves like a real PSP (pending, then
| succeeded / failed / unknown) so every journey, refund and ledger posting can be built and tested now.
*/

return [
    'default' => env('RAIL_DEFAULT', 'simulator'),

    // The rail that collects money for "Add money" (wallet top-ups). Lets real M-PESA collections run while payouts
    // stay on the default rail, e.g. RAIL_COLLECT=daraja before B2C credentials exist. Empty = the default rail.
    'collect' => env('RAIL_COLLECT'),
    // The rail that pays out (B2C / B2B), e.g. RAIL_PAYOUT=daraja once the initiator credentials are in. Empty = default.
    'payout' => env('RAIL_PAYOUT'),
    // While testing real collections on a live site, only these account emails use it (comma-separated); empty = all.
    'collect_only_for' => array_filter(array_map('trim', explode(',', (string) env('RAIL_COLLECT_ONLY_FOR', '')))),

    'adapters' => [
        'simulator' => App\Integrations\Simulator\SimulatorRail::class,
        // 'cashia' => App\Integrations\Cashia\CashiaRail::class,   (to integrate)
        'daraja' => App\Integrations\Daraja\DarajaRail::class,     // collections (STK push); payouts need B2C credentials
    ],

    // Ledger accounts that mirror each rail's real money: where collections land and where payouts leave from.
    'accounts' => [
        'simulator' => ['collect' => '1130', 'payout' => '1130'],
        'cashia' => ['collect' => '1130', 'payout' => '1130'],
        'gladys' => ['collect' => '1140', 'payout' => '1140'],
        // collections land in the paybill's working account, payouts leave its utility account (move float between
        // them in the M-PESA portal, then record it: php artisan ledger:float 1110 1120 <amount>)
        'daraja' => ['collect' => '1110', 'payout' => env('RAIL_DARAJA_PAYOUT_ACCOUNT', '1120')],
    ],

    'simulator' => [
        // seconds before a simulated request settles (0 in tests)
        'delay' => (int) env('RAIL_SIMULATOR_DELAY', 3),
        // Outcomes by the last four digits of the number (payer for collections, recipient for payouts):
        //   0000 → fails · 9999 → no answer for the first 2 status checks, then succeeds · anything else → succeeds
        'fail_suffix' => '0000',
        'slow_suffix' => '9999',
        'slow_checks' => 2,
        // a demo on the live server needs this on purpose; it never moves real money
        'allow_in_production' => (bool) env('RAIL_SIMULATOR_IN_PRODUCTION', false),
    ],

    // Rails that answer by callback: no status query until this many seconds have passed without one, so the callback
    // (with the full payload and the real receipt) decides, and a query never answers while the customer is on the PIN.
    'callback_wait' => ['daraja' => 60],

    // Payments still waiting this long get a status check from `ramp:resolve` (never a blind retry).
    'status_check_after_seconds' => 60,
];
