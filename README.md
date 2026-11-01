# Sharet Africa

Cheaper payments across East Africa. Sharet is a money platform for people and businesses: a wallet that sends to any
M-PESA number, paybill, till or bank account, receives from M-PESA and other Sharet users, and keeps every shilling on a
double-entry ledger. Live at [sharet.africa](https://sharet.africa).

> **Status:** v2 is in a controlled live test (simulated money for most accounts, real M-PESA collections for named
> testers). Personal accounts come first; business, merchant and trade-finance lanes follow.

## What it does

| Area | Today |
|---|---|
| **Wallet** | Balance, @name for wallet-to-wallet, receive by QR, add money from M-PESA, withdraw |
| **Payments** | Send to M-PESA numbers, paybills, tills and banks; transparent fees (free up to KES 100); Sharet PIN |
| **Ledger** | Double entry in integer cents; insert-only (database triggers); idempotent postings; holds for money in flight; hourly invariant checks |
| **Rails** | Safaricom Daraja (STK push collections, B2C / B2B payouts with the float held in escrow) and a simulator for testing; Cashia, Airtel Money, MTN MoMo and Vodacom M-Pesa Tanzania next |
| **Reconciliation** | Daily statement matching, suspense, maker-checker sign-off |
| **Customers** | Verification from ID photos and selfies (ID details read automatically), receipts (PDF, QR, email), SMS |
| **Staff** | Ledger, reconciliation, support desk (cases with SLAs), user management, integrations, analytics |

## Direction

1. **Now:** finish personal accounts end to end on real rails: M-PESA payouts, SMS and email, the scheduler and backups.
2. **Next:** risk service (Django) with fallback rules, staff consoles (operations, finance, compliance, kill switch),
   CI/CD with v1 and v2 deployed side by side, then hardening (load test, security review, restore drill).
3. **Then:** business and merchant accounts, more rails (Cashia, Airtel, MTN, Vodacom TZ), Uganda and Tanzania, trade finance.

The full plan, build order and decisions: [`docs/v2/README.md`](docs/v2/README.md). Where we left off:
[`docs/v2/NEXT.md`](docs/v2/NEXT.md).

## Versions and branches

| Branch | Version | Deploys to |
|---|---|---|
| `main` | v1, the original site | `sharet.africa` (kept deployable for audits) |
| `v2` | the money platform | `v2.sharet.africa`; `sharet.africa` during the current test |
| `archive/main-2026-09` | the September snapshot this repository started from | — |

v2 only **adds** to the database (it never alters a v1 table), so the live site can switch between v1 and v2 for an
audit: [`docs/v2/SWITCHING.md`](docs/v2/SWITCHING.md).

## Stack

Laravel 12 · PHP 8.3+ · MySQL / MariaDB · Alpine.js · Tailwind CSS · Vite · Spatie Permission · dompdf · ApexCharts.
Pages load once and talk JSON after that (no full reloads). Outside services sit behind adapters in `app/Integrations`,
with credentials stored encrypted in the database, never in code.

```
app/Ledger         the money core (no HTTP)
app/Integrations   payment rails, SMS, ID reading: one adapter per provider
app/Ramp           payments: quotes, states, escrow, refunds
app/Recon          reconciliation
app/Support        receipts, PIN, @names, KYC, customer messages
docs/v2            architecture and operations documentation
```

## Getting started

Requirements: PHP 8.3+, Composer, Node 20+, MySQL 8 or MariaDB 10.3+ (the tests run on SQLite).

```bash
composer install
npm install && npm run build
cp .env.example .env && php artisan key:generate
php artisan migrate
php artisan db:seed --class=Database\\Seeders\\LedgerAccountSeeder
php artisan serve
```

Payments use the simulator until a real rail is configured (`RAIL_DEFAULT`, `RAIL_COLLECT`, `RAIL_PAYOUT`). Provider keys
(M-PESA, KenyaSMS, SMTP) are entered in the app under **Integrations**, not in `.env`.

## Tests

```bash
php artisan test
```

The suite covers the ledger (including concurrency and the database triggers), every payment journey and failure, M-PESA
collections and payouts against a faked Safaricom, reconciliation, receipts, SMS, the support desk and access control.

## Deployment

`scripts/deploy.sh` backs up the live database and files, uploads a new release folder, migrates, swaps it in and checks
the main pages, swapping back if any fails. Stages, rollback, staging and the server's one-time setup are in
[`docs/v2/DEPLOY.md`](docs/v2/DEPLOY.md). GitHub Actions (tests on every push, `main` and `v2` deploying to their own
sites) is the next step.

## Security

- Queries go through Laravel's query builder with bound values; CSRF protection on every form; HTTPS only.
- The ledger is insert-only at the database level: money history can't be rewritten, even by the app's own login.
- Wallet money leaves only with the customer's Sharet PIN; M-PESA money only with their M-PESA PIN.
- Secrets live in the server's `.env` and in encrypted integration rows. Never commit `.env`, keys or database dumps.
- Nightly database backups are kept outside the web folder, readable only by the site's user.

Found a vulnerability? Email **support@sharet.africa** with "Security" in the subject rather than opening an issue.

## Contributing

- Branch from `v2` for platform work (`feature/<short-name>`); branch from `main` only for v1 fixes, and merge them into
  `v2` too.
- Follow the rules in [`docs/v2/README.md`](docs/v2/README.md): the five-file layout per area, no full page reloads,
  external APIs only through `app/Integrations`, money only through the ledger, additive migrations.
- Every change ships with tests; `php artisan test` must pass before a merge.

## Documentation

| Document | About |
|---|---|
| [`docs/v2/README.md`](docs/v2/README.md) | Rules, file tree, documentation plan, build order |
| [`docs/v2/LEDGER.md`](docs/v2/LEDGER.md) | Accounts, journals, holds, invariants |
| [`docs/v2/ORDERS.md`](docs/v2/ORDERS.md) | Payment states, wallet payments, PIN, tariff, M-PESA payouts and float escrow |
| [`docs/v2/RAILS.md`](docs/v2/RAILS.md) | The payment rail interface and adapters |
| [`docs/v2/RECONCILIATION.md`](docs/v2/RECONCILIATION.md) | Statement matching and sign-off |
| [`docs/v2/CUSTOMER_APP.md`](docs/v2/CUSTOMER_APP.md) | The customer app |
| [`docs/v2/KYC.md`](docs/v2/KYC.md) | Verification and ID reading |
| [`docs/v2/RECEIPTS.md`](docs/v2/RECEIPTS.md) | Receipts, PDF, email and SMS |
| [`docs/v2/DEPLOY.md`](docs/v2/DEPLOY.md) | Servers, deployment, backups |
| [`docs/v2/SWITCHING.md`](docs/v2/SWITCHING.md) | Running v1 and v2 side by side |

## License

Proprietary. © Sharet Africa. All rights reserved.
