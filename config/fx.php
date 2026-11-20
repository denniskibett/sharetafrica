<?php

/*
 * Foreign exchange. Sharet is strictly FIAT: no virtual assets of any kind (no VASP licence in Kenya), so only
 * ISO 4217 currencies below can ever be quoted, held or converted.
 *
 * The reference rate is Safaricom's M-PESA rate for the day (what M-PESA Global trades at), recorded by treasury
 * each morning: php artisan fx:rate KES/UGX 28.6500. Customers are quoted that rate less Sharet's margin, so every
 * conversion favours Sharet; amounts paid out are rounded down. No rate for today = no quote (never a stale one).
 */
return [
    'source' => 'safaricom',
    'currencies' => ['KES', 'UGX', 'TZS', 'RWF', 'USD', 'EUR', 'GBP', 'CNY', 'AED', 'INR', 'ZAR'],
    'pairs' => ['KES/UGX', 'KES/TZS', 'KES/RWF', 'KES/USD'],
    // Sharet's margin on the reference rate, in basis points (150 = 1.5%); per pair if set
    'margin_bps' => (int) env('FX_MARGIN_BPS', 150),
    'pair_margin_bps' => ['KES/USD' => (int) env('FX_MARGIN_BPS_USD', 200)],
    'quote_minutes' => 15,
    // A new day's rate more than this far from yesterday's must be confirmed (typo guard)
    'max_daily_move_pct' => 5,
    'timezone' => 'Africa/Nairobi',
];
