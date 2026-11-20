# FX: fiat only, Safaricom's daily rate, margin to Sharet

**Strictly fiat.** Sharet holds no virtual-asset licence (VASP) in Kenya, so no crypto, stablecoins or tokens,
ever. `config/fx.php` lists the only currencies the system accepts (ISO 4217); anything else is refused with
`not_fiat`, in quotes and in rate entry.

**Reference rate.** Each morning treasury records Safaricom's M-PESA rate for every offered pair:

    php artisan fx:rate KES/UGX 28.6500
    php artisan fx:rate               # today's rates, customer rates and margins

Rates are never edited; a correction is a new row the same day (the latest wins). A move of more than 5% from
yesterday needs `--confirm` (typo guard). No rate for today = no quote: cross-currency payments pause rather
than use a stale rate. `sharet:golive` warns when a pair is missing.

**Customer rate.** Reference rate less Sharet's margin (`FX_MARGIN_BPS`, default 150 = 1.5%; KES/USD 200),
in both directions, and every amount paid out rounds **down**. The quote returns `spread` (what Sharet keeps,
in the destination currency) for booking to FX income, and locks for 15 minutes.

**Published reference rates (display only).** `GET /api/v1/fx/rates` (public, no key, 60 a minute) returns the latest
reference rate per pair, no margin, for Sharet's own products (Breve, scharge) to *show* converted figures:

    {"base":"KES","date":"2026-11-20","pairs":{"KES/USD":"0.00770000"},"dates":{"KES/USD":"2026-11-20"},
     "stale":[],"source":"safaricom","margin":"none","use":"display_only"}

A pair whose latest rate is not today's is listed in `stale`. Nothing is quoted, held or converted through it: a real
conversion is always a quote above (margin, 15-minute lock), and none is offered without today's rate.

Code: `app/Fx/Rates.php`, `app/Http/Controllers/Api/V1/FxRatesController.php`, tests in `tests/Feature/Fx/RatesTest.php` and `tests/Feature/Api/FxRatesEndpointTest.php`.
