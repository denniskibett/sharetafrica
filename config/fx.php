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
    // Sharet's markup on the reference rate, by direction (owner, 22 Nov 2026), in basis points:
    //   buy  (the customer buys foreign currency with KES): pays the reference plus 1.5% (150)
    //   sell (the customer sells foreign currency for KES): gets the reference less 3.5% (350)
    'buy_markup_bps' => (int) env('FX_BUY_MARKUP_BPS', 150),
    'sell_markdown_bps' => (int) env('FX_SELL_MARKDOWN_BPS', 350),
    'quote_minutes' => 15,
    // A new day's rate more than this far from yesterday's must be confirmed (typo guard)
    'max_daily_move_pct' => 5,
    'timezone' => 'Africa/Nairobi',
];
