# sharet.africa v2

v2 is the same product as v1 (site, dashboard, KYC, integrations, API keys, analytics) plus the money
platform: a foolproof double-entry ledger, ramp orders, payment rails behind one interface, a risk
service and staff consoles. It lives on the `v2` branch. v1 stays on `main`, deployable at any time, and
the live site can be switched back to v1 for an audit (see SWITCHING.md).

Decisions behind it: the plan at https://claude.ai/artifact/CCFL4ADuzYMUkW1z6JhACV (sections U1–U13)
and the designs at https://claude.ai/artifact/87358vsvsCyhfmCKpEyCgn.

## Rules for every change

1. **Five-file areas** (the aiyotickets layout). Each area has:
   - `resources/views/<area>/index.blade.php` and `show.blade.php`
   - `resources/views/<area>/partials/*` for pieces only that area uses
   - shared pieces as components: `x-ui.card`, `x-ui.chart`, `x-ui.table`, `x-ui.slide-over` (panel/modal), `x-ui.stat`
   - one model and one controller (`app/Http/Controllers/<Area>Controller.php`)
2. **No full page reloads.** Lists load as JSON through `App\Support\Table` + `dataTable()` (resources/js/dashboard.js);
   forms submit with fetch and refresh the list in place; panels open with `sharet.panel()`. Controllers answer JSON when
   `$request->expectsJson()` and render the page otherwise.
3. **No unnecessary code.** No new front-end framework, no demo bundles, no package for something a 20-line class does.
   Load heavy scripts only on pages that use them.
4. **External APIs only through the integrations side.** Credentials live in the encrypted `integrations` rows
   (`App\Services\Integrations`); calls go through adapters in `app/Integrations/` (one per provider). Controllers never
   call a provider directly.
5. **Only the ledger writes money.** Money moves only through `App\Ledger\Ledger`. Postings are insert-only (the
   database refuses UPDATE and DELETE). Amounts are whole cents (`App\Ledger\Money`), shown with 2 decimals.
6. **v2 migrations are additive.** New tables only (`ledger_*`, `ramp_*`, `risk_*`); never alter or drop a v1 table, so
   v1 keeps working on the same database when switched back.

## Tree (v2 additions; everything else is v1 as it is)

```
app/
  Ledger/                      the money core, no HTTP
    Ledger.php                 post(), reverse(), hold(), capture(), release(), balance()
    Money.php                  cents in, "1,000.00" out, percentages with half-up rounding
    Invariants.php             the checks behind `php artisan ledger:check`
    LedgerException.php        one exception type with a reason code
  Integrations/                one adapter per payment provider (docs/v2/RAILS.md)
    Contracts/PaymentRail.php  collect, payout, status
    RailResult.php  Rails.php  the answer type and the registry (config/rails.php)
    Simulator/SimulatorRail.php  stand-in PSP until Cashia and Daraja (no real money)
    Cashia/  Daraja/  Gladys/  (to integrate)
  Ramp/                        personal "Pay anyone" orders (docs/v2/ORDERS.md)
    RampOrders.php             quote, confirm, refresh, apply (the only place orders change)
    Pricing.php  Destination.php
  Models/
    LedgerAccount.php  LedgerJournal.php  LedgerPosting.php  LedgerHold.php  RailRequest.php  RampOrder.php
  Http/Controllers/
    LedgerController.php       staff ledger area: accounts, trial balance, account statement, journal
    PayController.php          personal Pay anyone: quote, confirm, follow
  Console/Commands/
    LedgerCheck.php            ledger:check (hourly; alerts on any failure)
    RampResolve.php            ramp:resolve (every minute; status checks, quote expiry)
    ReconRun.php               recon:run (daily 00:30; import and match yesterday's statement)
  Recon/Reconciler.php         statement matching, fix from statement, suspense, maker-checker sign-off
  Providers/
    V2ServiceProvider.php      wires routes/v2.php, commands and the schedule; v1 files stay untouched
config/
  ledger.php                   chart of accounts and safeguarding groups
  rails.php  pricing.php       payment rails; fees and limits (placeholders until the Cashia tariff)
database/migrations/
  2026_11_01_100000_create_ledger_tables.php   tables + insert-only triggers
  2026_11_01_110000_create_rail_requests_and_ramp_orders.php
database/seeders/
  LedgerAccountSeeder.php      creates the chart of accounts
resources/views/ledger/
  index.blade.php              accounts and trial balance (JSON table, no reload)
  show.blade.php               one account: balance and statement (JSON table)
  partials/journal.blade.php   journal detail in the slide-over
resources/views/pay/
  index.blade.php  show.blade.php  partials/form.blade.php  partials/status.blade.php
resources/views/reconciliation/
  index.blade.php  show.blade.php  partials/run.blade.php  partials/signoff.blade.php
routes/
  v2.php
scripts/
  switch-version.sh            point the live site at v1 or v2
tests/
  Feature/Ledger/LedgerTest.php       posting, refusals, idempotency, holds, reversals, triggers, ledger:check
  Feature/Ledger/LedgerPagesTest.php  staff pages and JSON, access control
  Unit/MoneyTest.php                  parsing, formatting, rounding
  Feature/Ramp/RampOrdersTest.php     every journey, failure, refund, duplicate and limit
  Feature/Recon/ReconciliationTest.php  clean day, missed callback, suspense, two-person sign-off, screens
.sharet-version                       "v2" (v1 has no file)
docs/v2/
  README.md  LEDGER.md  SWITCHING.md  RAILS.md  ORDERS.md  RECONCILIATION.md
```

## Documentation plan

| Document | Covers | Status |
|---|---|---|
| `docs/v2/README.md` | This file: rules, tree, plan | done |
| `docs/v2/LEDGER.md` | Accounts, journals, postings, holds, idempotency, invariants, how to post each journey | done |
| `docs/v2/SWITCHING.md` | Running v1 and v2 side by side and switching for an audit | done |
| `docs/v2/RAILS.md` | The PaymentRail interface, the simulator, how to add Cashia, callbacks, status checks | done (simulator) |
| `docs/v2/ORDERS.md` | Ramp order states, quotes, refunds, the unknown-state worker | done |
| `docs/v2/RECONCILIATION.md` | Statement import, matching, breaks, suspense, sign-off | done |
| `docs/v2/RISK.md` | The Django risk service, score bands, fallback rules | with the risk service |
| `docs/v2/RUNBOOKS.md` | Outages, reversals, late money, daily close, kill switch | before launch |
| `docs/v2/DEPLOY.md` | Servers, CI, release steps (amazonblue deploy pattern) | before launch |

## Local copy of the live database

`~/sharet-live/` holds dumps of the live database (outside any repo, readable by you only). The v2 worktree's `.env`
points at the local copy `sharet_live_copy`; v1's local database is untouched. A local-only test login is in
`~/sharet-live/local-test-login.txt`. Refresh the copy by re-running the dump and import.

## Build order (phase 1, personal accounts)

1. Ledger core, tests, staff ledger area — **done**
2. Payment rails interface — **done, with the simulator**; Cashia and Daraja adapters next (integrations side)
3. Ramp orders: quote, states, idempotency, refunds, unknown-state worker — **done**
4. Personal flows: pay a phone, paybill, till, bank — **done** (receipts by SMS next)
5. Reconciliation: statement import, matching, suspense, daily sign-off — **done**
6. Risk service (Django) + fallback rules in Laravel
7. Staff consoles: support, operations, finance, compliance, kill switch
8. Hardening: load test, security review, restore drill
