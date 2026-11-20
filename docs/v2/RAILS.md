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
