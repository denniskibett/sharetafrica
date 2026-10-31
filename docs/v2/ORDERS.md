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

## Wallet payments (dashboard money panel)

The customer dashboard's right-hand panel (`dashboard/partials/money`) does everything; `/pay` is the history.

| Action | How | Ledger |
|---|---|---|
| Receive from M-PESA (add money) | `POST /pay/top-up`, confirmed like any order (STK) | Dr rail / Cr 2100 wallet. Free |
| Receive from a Sharet wallet | the sender pays your `@handle` (shown with a QR code) | see transfer |
| Send to an @handle | `POST /pay/transfer`, instant, no rail | Dr 2100 sender / Cr 2100 recipient. Free |
| Send to phone, paybill, till or bank | `POST /pay/quote` with `funding` mpesa or wallet | wallet: Dr 2100 / Cr 2200 on confirm, then the payout; a failed payout returns to the wallet |
| Withdraw | a wallet payment to your own number (B2C) or bank | as above |

M-PESA destinations follow Daraja: B2C to a phone, B2B to a paybill or till, and banks through their paybill.

## Tariff (config/pricing.php)

Revenue share: Safaricom's charge for the amount band plus our markup (`markup_bp`, 20%), excise (15%) on the markup only.
Up to KES 100 is free. Example: 985.00 → 13.00 + 2.60 = 15.60 fee, 0.39 excise, pay 1,001.00.
Check the bands against Safaricom's current tariff sheet (and the partner's rate) before going live.

## Sharet PIN (App\Support\TransactionPin)

Money leaving a wallet (wallet payments, withdrawals, @name transfers) needs the customer's 4-digit Sharet PIN:
`POST /pay/pin` sets it (first time; changing it needs the current one), and `confirm` / `transfer` check it. Stored
hashed; easy PINs (1234, 0000…) refused; 5 wrong tries pause wallet payments for 15 minutes. Money from M-PESA is
approved by the M-PESA PIN on the phone, so no Sharet PIN is asked.
