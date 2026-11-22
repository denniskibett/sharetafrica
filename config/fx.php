<?php

/*
 * Foreign exchange. Sharet is strictly FIAT: no virtual assets of any kind (no VASP licence in Kenya), so only
 * ISO 4217 currencies below can ever be quoted, held or converted.
 *
 * The reference rate is Safaricom's M-PESA rate for the day (what M-PESA Global trades at), recorded by treasury
 * each morning: php artisan fx:rate KES/UGX 28.6500 (KES/USD comes from the Central Bank of Kenya: fx:cbk). Customers buy
 * foreign currency at that rate plus a markup and sell it at that rate less a markdown, so every
 * conversion favours Sharet; amounts paid out are rounded down. No rate for today = no quote (never a stale one).
 */
return [
    'source' => 'safaricom',
    'currencies' => ['KES', 'UGX', 'TZS', 'RWF', 'USD', 'EUR', 'GBP', 'CNY', 'AED', 'INR', 'ZAR'],
    'pairs' => ['KES/UGX', 'KES/TZS', 'KES/RWF', 'KES/USD'],
    // Sharet's markup on the reference rate, by direction (owner, 22 Nov 2026: "don't be hungry and chase clients off").
    // Dollars: a fixed number of shillings per dollar. Buying a dollar costs CBK + KES 1.30; selling one fetches
    // CBK − KES 3.00 (CBK 130.00 → buy 131.30, sell 127.00).
    'fixed_kes' => [
        'USD' => ['buy' => env('FX_USD_BUY_KES', '1.30'), 'sell' => env('FX_USD_SELL_KES', '3.00')],
    ],
    // Other currencies (UGX, TZS, RWF), in basis points of the reference: buy +1.5%, sell −3.5% (owner: keep these).
    'buy_markup_bps' => (int) env('FX_BUY_MARKUP_BPS', 150),
    'sell_markdown_bps' => (int) env('FX_SELL_MARKDOWN_BPS', 350),
    'quote_minutes' => 15,
    // A new day's rate more than this far from yesterday's must be confirmed (typo guard)
    'max_daily_move_pct' => 5,
    'timezone' => 'Africa/Nairobi',
];
