# FX: fiat only, Safaricom's daily rate, margin to Sharet

**Strictly fiat.** Sharet holds no virtual-asset licence (VASP) in Kenya, so no crypto, stablecoins or tokens,
ever. `config/fx.php` lists the only currencies the system accepts (ISO 4217); anything else is refused with
`not_fiat`, in quotes and in rate entry.

**Reference rate.** Each morning treasury records Safaricom's M-PESA rate for every offered pair:

    php artisan fx:rate KES/UGX 28.6500
    php artisan fx:rate               # today's rates, customer rates and margins

**KES/USD comes from the Central Bank of Kenya, automatically** (owner, 22 Nov 2026). `php artisan fx:cbk` reads
CBK's daily indicative mean rate from the "Daily KES Exchange Rates" panel on centralbank.go.ke ("US DOLLAR 130.00 …
Posted On: 20-11-2026") and records it as today's KES/USD reference (source `cbk`). The scheduler runs it at 09:30,
12:00 and 15:00 Nairobi time; a later run the same day records nothing new. It never replaces a rate treasury set
by hand that day (`fx:cbk --force` does), and a jump past the typo guard is not recorded (logged; treasury checks it
and uses `fx:cbk --confirm` or `fx:rate`). Weekends and holidays: CBK's last posted rate is recorded for the day.
The scheduler needs the server's cron line: `* * * * * cd /home/sharet.africa/public_html && sudo -u share6158 php artisan schedule:run`.

Rates are never edited; a correction is a new row the same day (the latest wins). A move of more than 5% from
yesterday needs `--confirm` (typo guard). No rate for today = no quote: cross-currency payments pause rather
than use a stale rate. `sharet:golive` warns when a pair is missing.

**Customer rate** (owner, 22 Nov 2026: "don't be hungry and chase clients off"). **Dollars** carry a fixed number of
shillings: buying a dollar costs the reference **+ KES 1.30** (`FX_USD_BUY_KES`), selling one fetches the reference
**− KES 3.00** (`FX_USD_SELL_KES`). With CBK at USD 1 = KES 130.00: buy 131.30, sell 127.00. **Other currencies**
(UGX, TZS, RWF) stay in percent: buy at the reference **+ 1.5%** (`FX_BUY_MARKUP_BPS`, 150), sell at **− 3.5%**
(`FX_SELL_MARKDOWN_BPS`, 350). Every amount paid out rounds **down**. The quote returns `side`, `markup_kes` or
`markup_bps`, and `spread` (what Sharet keeps, in the destination currency) for booking to FX income, and locks for
15 minutes. The app and dashboard line "USD 1 = KES 131.30" is the buying price; `php artisan fx:rate` lists both.

**Published reference rates (display only).** `GET /api/v1/fx/rates` (public, no key, 60 a minute) returns the latest
reference rate per pair, no margin, for Sharet's own products (Breve, scharge) to *show* converted figures:

    {"base":"KES","date":"2026-11-22","pairs":{"KES/USD":"0.00769230"},"rates":{"USD":"0.00769230"},
     "kes_per":{"USD":"130.0001"},"dates":{"KES/USD":"2026-11-22"},"sources":{"KES/USD":"cbk"},
     "stale":[],"source":"cbk","margin":"none","use":"display_only"}

`rates` (units per KES 1) and `kes_per` (KES per unit) are the same figures keyed by currency, for products that
want them that way; `pairs` is unchanged, so existing readers keep working.

A pair whose latest rate is not today's is listed in `stale`. Nothing is quoted, held or converted through it: a real
conversion is always a quote above (margin, 15-minute lock), and none is offered without today's rate.

Code: `app/Fx/Rates.php`, `app/Http/Controllers/Api/V1/FxRatesController.php`, tests in `tests/Feature/Fx/RatesTest.php` and `tests/Feature/Api/FxRatesEndpointTest.php`.
