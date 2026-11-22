<?php

/*
 * Phase 2 · Business and Trade in the app. Sample figures: confirm before real customers (docs/v2/DECISIONS-FOR-APPROVAL.md §0f).
 * Amounts in cents.
 */
return [
    // A batch above this needs two different checkers (canvas: "above [LIMIT] two checkers")
    'two_checkers_above' => (int) env('BUSINESS_TWO_CHECKERS_ABOVE', 100_000_000),      // KES 1,000,000.00
    // A first payment to someone new waits this long unless the owner approves the batch
    'new_payee_hours' => (int) env('BUSINESS_NEW_PAYEE_HOURS', 24),
    'max_lines' => 500,
    // A checker added without a limit of their own approves up to this
    'default_checker_limit' => (int) env('BUSINESS_DEFAULT_CHECKER_LIMIT', 50_000_000),   // KES 500,000.00

    'trade' => [
        // One LPO / invoice payment, wallet to wallet
        'max_order' => (int) env('TRADE_MAX_ORDER', 1_000_000_000),                       // KES 10,000,000.00
        'terms' => ['on_delivery' => 'Pay on delivery', '30_days' => '30 days', '60_days' => '60 days'],
        // Payments abroad: currencies a supplier abroad can be quoted in, and the partner that pays them. None is signed
        // yet (Cloud9 is KES only; abroad needs a bank partner for SWIFT, or a mobile-money partner for UGX/TZS/RWF), so
        // a quote can be locked but never paid. Set a currency's partner only once one is live.
        'abroad' => [
            'USD' => ['countries' => ['US', 'CN', 'AE', 'IN', 'GB', 'ZA', 'UG', 'TZ', 'RW'], 'partner' => null, 'needs' => 'a bank partner for SWIFT'],
            'UGX' => ['countries' => ['UG'], 'partner' => null, 'needs' => 'a payout partner in Uganda'],
            'TZS' => ['countries' => ['TZ'], 'partner' => null, 'needs' => 'a payout partner in Tanzania'],
            'RWF' => ['countries' => ['RW'], 'partner' => null, 'needs' => 'a payout partner in Rwanda'],
        ],
        // Trade finance (lenders offering on an invoice): off until lender agreements and a credit licence check exist
        'finance' => false,
    ],
];
