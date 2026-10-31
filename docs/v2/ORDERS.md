# Ramp orders (personal "Pay anyone")

A customer pays from M-PESA; Sharet pays the recipient straight away. No balance is held (plan U1, U7).
`App\Ramp\RampOrders` is the only place an order changes state.

```
quoted ──confirm──▶ collecting ──M-PESA paid──▶ paying ──recipient paid──▶ paid
   │                    │                          │
   └─10 min─▶ expired   └─cancelled─▶ failed       └─payout failed─▶ refunding ──▶ refunded
                                                                          └─refund failed─▶ refund_failed (ops)
```

| Step | Ledger |
|---|---|
| Quote, confirm, waiting | nothing |
| Collection confirmed | Dr rail (1130) / Cr 2200 order · total |
| Payout confirmed | Dr 2200 total / Cr rail amount, Cr 4100 fee, Cr 2300 excise, Cr 4900 rounding |
| Refund confirmed | Dr 2200 total / Cr rail total (no fee kept) |

Every order has its own 2200 account, back to 0.00 when it ends; `ledger:check` warns about any still holding money
after 30 minutes.

**Pricing** (`config/pricing.php`, placeholder until the Cashia tariff): fee 1% of the amount, between 10.00 and 100.00;
excise 15% of the fee on top; the payer pays whole shillings, the difference goes to 4900 Rounding.

**Limits** by KYC level: none → cannot pay; basic → 70,000.00 per payment and per day; standard → 250,000.00 per payment,
500,000.00 per day (all placeholders to confirm).

**Never twice:** confirming twice sends one prompt; one rail request per step; no resend on silence, only status checks
(`php artisan ramp:resolve`, every minute, also expires old quotes); `apply()` is idempotent.

**Screens:** `/pay` (form, quote, live progress, list of payments) and `/pay/{uuid}` (one payment following itself).
Both poll JSON every 2 seconds while the payment moves; nothing reloads.

**Tests:** `tests/Feature/Ramp/RampOrdersTest.php` covers pricing, all destination types, cancelled prompt, failed payout
with refund, silent provider, duplicate callbacks, double confirm, expiry, validation and limits, the resolver, the
production guard and the pages.
