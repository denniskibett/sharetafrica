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
  Integrations/                (next) one adapter per payment provider
    Contracts/PaymentRail.php  collect, payout, status, reversal, balance, statement, verifyWebhook
    Cashia/  Gladys/  Daraja/
  Models/
    LedgerAccount.php  LedgerJournal.php  LedgerPosting.php  LedgerHold.php
  Http/Controllers/
    LedgerController.php       staff ledger area: accounts, trial balance, account statement, journal
  Console/Commands/
    LedgerCheck.php            ledger:check (run hourly; alerts on any failure)
  Providers/
    V2ServiceProvider.php      wires routes/v2.php, commands and the schedule; v1 files stay untouched
config/
  ledger.php                   chart of accounts and safeguarding groups
database/migrations/
  2026_11_01_100000_create_ledger_tables.php   tables + insert-only triggers
database/seeders/
  LedgerAccountSeeder.php      creates the chart of accounts
resources/views/ledger/
  index.blade.php              accounts and trial balance (JSON table, no reload)
  show.blade.php               one account: balance and statement (JSON table)
  partials/journal.blade.php   journal detail in the slide-over
routes/
  v2.php
scripts/
  switch-version.sh            point the live site at v1 or v2
tests/
  Feature/Ledger/LedgerTest.php       posting, refusals, idempotency, holds, reversals, triggers, ledger:check
  Feature/Ledger/LedgerPagesTest.php  staff pages and JSON, access control
  Unit/MoneyTest.php                  parsing, formatting, rounding
.sharet-version                       "v2" (v1 has no file)
docs/v2/
  README.md  LEDGER.md  SWITCHING.md
```

## Documentation plan

| Document | Covers | Status |
|---|---|---|
| `docs/v2/README.md` | This file: rules, tree, plan | done |
| `docs/v2/LEDGER.md` | Accounts, journals, postings, holds, idempotency, invariants, how to post each journey | done |
| `docs/v2/SWITCHING.md` | Running v1 and v2 side by side and switching for an audit | done |
| `docs/v2/RAILS.md` | The PaymentRail interface, Cashia, Gladys, Daraja adapters, callbacks, status checks | with the rails |
| `docs/v2/ORDERS.md` | Ramp order states, quotes, refunds, the unknown-state worker | with the orders |
| `docs/v2/RISK.md` | The Django risk service, score bands, fallback rules | with the risk service |
| `docs/v2/RUNBOOKS.md` | Outages, reversals, late money, daily close, kill switch | before launch |
| `docs/v2/DEPLOY.md` | Servers, CI, release steps (amazonblue deploy pattern) | before launch |

## Build order (phase 1, personal accounts)

1. Ledger core, tests, staff ledger area — **this branch, first**
2. Payment rails interface + Cashia and Daraja adapters (integrations side)
3. Ramp orders: quote, states, idempotency, refunds, unknown-state worker
4. Personal flows: pay a phone, paybill, till, bank; receipts
5. Reconciliation: statement import, matching, suspense, daily sign-off
6. Risk service (Django) + fallback rules in Laravel
7. Staff consoles: support, operations, finance, compliance, kill switch
8. Hardening: load test, security review, restore drill
