<?php

/*
| Merchant and cashier (Phase 2). Every figure here is a proposal waiting for the owner (DECISIONS-FOR-APPROVAL.md, M1–M8).
*/

return [
    // Sharet's merchant fee per sale, in basis points of the sale (150 = 1.5%), excise inside it. By how it was paid.
    'fee_bp' => [
        'mpesa' => (int) env('MERCHANT_FEE_MPESA_BP', 150),      // M-PESA prompt, QR or link paid by M-PESA
        'account' => (int) env('MERCHANT_FEE_ACCOUNT_BP', 150),  // paid to the shop's account number from the M-PESA menu
        'sharet' => (int) env('MERCHANT_FEE_SHARET_BP', 0),      // paid from a Sharet wallet
    ],

    // One sale a cashier can charge (KES); the owner charges up to the shop's own limits.
    'cashier_max_sale' => (int) env('MERCHANT_CASHIER_MAX_SALE', 150_000),

    // A cashier can ask for a refund of a sale on their own till up to this many days after it.
    'refund_days' => (int) env('MERCHANT_REFUND_DAYS', 30),

    // Whether Sharet's fee on a sale goes back to the shop when the sale is refunded in full.
    'refund_returns_fee' => (bool) env('MERCHANT_REFUND_RETURNS_FEE', false),

    // A cashier invite code works this long; the cashier PIN opens a till for this many hours.
    'invite_hours' => 48,
    'session_hours' => 14,

    // End of day: tills close at the shop's time (default below). Settlement (bank or M-PESA) runs only when switched on.
    'settle_at' => '17:00',
    'auto_settle' => (bool) env('MERCHANT_AUTO_SETTLE', false),

    'timezone' => 'Africa/Nairobi',
];
