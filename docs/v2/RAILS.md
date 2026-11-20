# Payment rails

Every payment provider is an adapter behind `App\Integrations\Contracts\PaymentRail`:

| Method | Does | Answers |
|---|---|---|
| `collect($request)` | Asks the payer to pay (STK push) | usually `pending` |
| `payout($request)` | Pays a phone, paybill, till or bank account | usually `pending` |
| `status($request)` | Asks the provider what happened, by our reference | `pending`, `succeeded`, `failed` or `unknown` |

Rules for every adapter:
- Credentials come from the encrypted integrations rows (`App\Services\Integrations`), never from code or `.env` directly.
- `$request->reference` is our idempotency key: send it to the provider so a retry can never pay twice.
- An adapter never posts to the ledger and never changes an order. It reports; `App\Ramp\RampOrders::apply()` decides.
- A timeout or crash means `unknown`: the order waits for a status check. Nothing is ever re-sent.
- Callbacks: verify the provider's signature or IP, store the raw payload, then call `RampOrders::apply()` with the
  matching `RailRequest`. `apply()` is idempotent, so a callback and a status check arriving together post once.

`config/rails.php` picks the default rail and maps each rail to the ledger accounts that mirror its money
(simulator and Cashia → 1130; Gladys → 1140; Daraja → 1110 for collections, 1120 for payouts).

## The simulator (until Cashia and Daraja are integrated)

`App\Integrations\Simulator\SimulatorRail` answers like a real PSP and moves no money:

| Number ends in | Collection (payer) | Payout (recipient) |
|---|---|---|
| `0000` | fails ("customer cancelled") | fails → full refund |
| `9999` | no answer for the first 2 status checks, then succeeds | same |
| anything else | succeeds after `RAIL_SIMULATOR_DELAY` seconds (3) | same |

It refuses to run in production unless `RAIL_SIMULATOR_IN_PRODUCTION=true` is set on purpose (for a demo).

## Adding Cashia (next)

1. `app/Integrations/Cashia/CashiaRail.php` implementing the three methods against Cashia's API; credentials from a
   `cashia` integration (add it to `config/integrations.php`).
2. A callback route that verifies Cashia's webhook signature and calls `RampOrders::apply()`.
3. Register it in `config/rails.php` → `adapters`, set `RAIL_DEFAULT=cashia` in staging, run the ramp tests against the
   Cashia sandbox.

## Cloud9 (built 20 Nov 2026)

Every payment goes through Sharet first (one Sharet reference, one ledger, one receipt); Cloud9 only moves the money.
Customers never see the name Cloud9: the M-PESA prompt shows the Sharet reference (Cloud9 prefixes `STK-` and
Safaricom cuts the account at 12 characters, so `STK-SHUK7Q2M`) and "Sharet"; failure text has the name taken out.

- **Adapter** `app/Integrations/Cloud9/Cloud9Rail.php`: money in = STK push (`payin/deposits/init/`, KES 20 minimum);
  money out = `payout/payouts/init/` (KES 10 minimum): B2C to a phone, B2B to a paybill or till, a bank through its
  M-PESA paybill (`config/pricing.php` `bank_paybills`; PesaLink is sandbox-only at Cloud9). Status = the deposit or
  payout read by its id. Statement (nightly `recon:run --rail=cloud9`, 00:40) = Cloud9's successful deposits and
  payouts for the day, matched by the Cloud9 id.
- **Webhooks** `POST /rails/cloud9/callback` (sent with every request as `callback_url`): believed only when
  `X-Cloud9-Callback-Secret` matches the PAYIN or PAYOUT secret in Integrations › Cloud9, then re-read by id before booking.
- **Three references per payment:** Sharet's (`SH…`, the order), the rail's (`rail_requests.provider_reference`, the
  Cloud9 id) and the network's own (`rail_requests.receipt`: the M-PESA receipt, bank or card reference), with the
  network named in `rail_requests.meta.network` (M-PESA, PesaLink (bank), Card, Airtel Money).
- **Ledger:** Sharet's one MAIN KES wallet at Cloud9 = account **1160** (safeguarded). Cloud9 takes its charge out of
  a deposit (1160 gets the net). On a **wallet top-up the customer pays it** (you decided, 20 Nov): the wallet gets the
  net, and the order shows total = paid by M-PESA, fee = the charge, amount = what reached the wallet ("Added KES 98.50
  · payment charge 1.50"). On a payment collected to send on, and on payouts (Cloud9 adds its charge on top: 1160
  pays amount + charge), the charge comes out of the Sharet fee the customer was shown (Sharet's cost, **5130**).
- **The switch** (System › Payment rails, `rail_routes`): per kind of payment (money in, to a phone, paybill/till,
  bank, business settlements) a primary rail, an optional fallback and who it applies to (test list or everyone).
  One person proposes with a reason, a different person approves with their password; both logged. No route = the
  old `.env` behaviour. A fallback is tried only when the primary refuses before accepting (no Cloud9 id); after a
  timeout the request stays `unknown` and is checked by id, never resent elsewhere.
- **Sandbox checks** on the same page: read wallets, a KES 20 prompt and a KES 10 payout to the staff member's own
  number (sandbox only), and a status read by Cloud9 id. Activity: System › Payment rails › Activity.

To put every payment on Cloud9: enter the live key and the two callback secrets, have Cloud9 allow-list the server
address, then approve a route to Cloud9 with "Everyone" for each kind of payment.

