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

## Tree (v2 additions and changes; everything else is v1 as it is)

`+` new in v2, `~` a v1 file v2 changes. Generated from `git diff --name-status main...v2`.

```
app/
  Ledger/                                  + the money core, no HTTP (LEDGER.md)
    Ledger.php                             + post, reverse, hold, capture, release, account
    Money.php  Invariants.php  LedgerException.php
  Integrations/                            + every outside service, one adapter each (RAILS.md, KYC.md)
    Contracts/PaymentRail.php              + collect, payout, status
    Contracts/ProvidesStatements.php       + statements for reconciliation
    Contracts/IdReader.php                 + read an ID photo
    Rails.php  RailResult.php              + rail registry (config/rails.php) and its answers
    Simulator/SimulatorRail.php            + stand-in PSP until Cashia / Daraja
    IdReader/TesseractIdReader.php         + OCR on this machine
    IdReader/KenyanIdText.php              + fields from Kenyan ID text (front labels, back MRZ)
    IdReader/NullIdReader.php              + no reader installed
  Ramp/                                    + payments (ORDERS.md)
    RampOrders.php                         + quote, confirm, top up, transfer, refresh, apply
    Pricing.php                            + Safaricom band + markup, free to KES 100
    Destination.php                        + phone, paybill, till, bank
  Recon/Reconciler.php                     + statement matching, suspense, two-person sign-off (RECONCILIATION.md)
  Support/
    Receipt.php                            + receipt PDF, QR text, receipt emails (RECEIPTS.md)
    TransactionPin.php                     + the Sharet PIN for wallet money
    Handle.php                             + @names for wallet-to-wallet
    Kyc.php                                ~ verified = ID photos + selfies; ID read on upload (KYC.md)
    DashboardNav.php                       ~ the customer menu
  Models/
    LedgerAccount  LedgerJournal  LedgerPosting  LedgerHold  RailRequest  RampOrder  StatementLine  ReconDay   +
    User.php                               ~ PIN fields
  Http/Controllers/
    LedgerController.php                   + staff ledger
    PayController.php                      + transactions, quote, confirm, top up, transfer, PIN, chart, receipt
    ReconciliationController.php           + staff reconciliation
    DashboardController.php                ~ customer home with the wallet
    ProfileController.php  UserController.php  WalletController.php (now redirects to the dashboard)   ~
  Mail/SharetMail.php                      ~ transaction receipt email
  Console/Commands/
    LedgerCheck.php  RampResolve.php  ReconRun.php   + ledger:check, ramp:resolve, recon:run
  Providers/V2ServiceProvider.php          + routes/v2.php, bindings, schedule; v1 wiring untouched
config/
  ledger.php  rails.php  pricing.php       + accounts; rails; tariff, limits, receipt threshold
  services.php                             ~ ID reader settings
database/
  migrations/2026_11_01_100000_create_ledger_tables.php                  + tables and insert-only triggers
  migrations/2026_11_01_110000_create_rail_requests_and_ramp_orders.php  +
  migrations/2026_11_01_120000_create_reconciliation_tables.php          +
  migrations/2026_11_01_130000_add_wallet_payments.php                   + funding, transfers, @names
  migrations/2026_11_01_140000_add_transaction_pin_to_users.php          + PIN and lockout
  seeders/LedgerAccountSeeder.php          + the chart of accounts
resources/
  css/app.css                              ~ customer theme (app-* colours, fonts, numbers in Outfit)
  js/app.js  js/dashboard.js               ~ bundled fonts, QR, intersect plugin
  fonts/                                   + Outfit and Inter Tight TTF for PDFs (OFL)
  views/
    layouts/app.blade.php                  ~ customer layout: top bar, tab bar, receipt panel (CUSTOMER_APP.md)
    partials/customer/topbar  tabbar  account-links                         ~
    partials/modal/slide-over.blade.php    ~ Back on phones closes it
    dashboard/customer.blade.php           ~ the customer home
    dashboard/partials/wallet  summary  money  money-bank                   +
    pay/index  show  receipt-pdf                                             + transactions, one payment, the PDF
    pay/partials/row  detail  status  steps  icon  chart                     +
    ledger/index  show  partials/journal                                    + staff ledger
    reconciliation/index  show  partials/run  partials/signoff              + staff reconciliation
    profile/*                              ~ verification first, buttons in cards
    components/ui/table.blade.php          ~ icon-only paging, rows per page in the header
routes/v2.php                              + every v2 route
scripts/
  deploy.sh                                ~ can target a staging site (DEPLOY.md)
  switch-version.sh                        + live on v1 or v2 (SWITCHING.md)
tests/
  Feature/Ledger/LedgerTest.php  LedgerPagesTest.php                     +
  Feature/Ramp/RampOrdersTest.php  WalletPaymentsTest.php                 +
  Feature/Recon/ReconciliationTest.php                                    +
  Unit/MoneyTest.php  Unit/KenyanIdTextTest.php                           +
  Feature/DashboardPagesTest.php  VerificationAndIntegrationsTest.php     ~
.sharet-version                            + "v2" (v1 has none)
docs/v2/                                   + this documentation
```

## Documentation plan

| Document | Covers | Status |
|---|---|---|
| `README.md` | This file: rules, tree, plan, build order | done |
| `NEXT.md` | Where we left off and what's waiting | living |
| `LEDGER.md` | Accounts, journals, postings, holds, idempotency, invariants | done |
| `ORDERS.md` | Payment states, quotes, refunds, wallet payments, Sharet PIN, tariff | done |
| `RAILS.md` | The PaymentRail interface, the simulator, adding Cashia / Daraja | done (simulator) |
| `RECONCILIATION.md` | Statement import, matching, suspense, sign-off | done |
| `CUSTOMER_APP.md` | Layout, theme, dashboard, money panel, transactions | done |
| `KYC.md` | Verification levels and reading the ID photo | done |
| `RECEIPTS.md` | Receipt lines, panel, PDF, QR, emails | done |
| `DEPLOY.md` | Servers, deployment stages, staging, rollback, the current live state | done |
| `SWITCHING.md` | v1 and v2 side by side, switching for an audit | done |
| `RISK.md` | The Django risk service, score bands, fallback rules | with the risk service |
| `RUNBOOKS.md` | Outages, reversals, late money, daily close, kill switch | before real money |

## Local copy of the live database

`~/sharet-live/` holds dumps of the live database (outside any repo, readable by you only). The v2 worktree's `.env`
points at the local copy `sharet_live_copy`; v1's local database is untouched. A local-only test login is in
`~/sharet-live/local-test-login.txt`. Refresh the copy by re-running the dump and import.

## Build order (phase 1, personal accounts)

1. Ledger core, tests, staff ledger area — **done**
2. Payment rails interface — **done, with the simulator**; Cashia and Daraja adapters next (integrations side)
3. Ramp orders: quote, states, idempotency, refunds, unknown-state worker — **done**
4. Personal flows: pay a phone, paybill, till, bank; wallet top-up, withdraw, @name transfers; Sharet PIN — **done**
5. Reconciliation: statement import, matching, suspense, daily sign-off — **done**
6. Customer app: wallet dashboard, money panel, transactions, receipts (PDF, email above a threshold) — **done**
7. Verification: ID photos + selfies verify; details read from the ID — **done** (Tesseract on the server next)
8. Risk service (Django) + fallback rules in Laravel
9. Staff consoles: support, operations, finance, compliance, kill switch
10. Hardening: load test, security review, restore drill, the scheduler cron on the server
