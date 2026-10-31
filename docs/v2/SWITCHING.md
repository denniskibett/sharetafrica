# Running v1 and v2, and switching for an audit

v1 is the `main` branch; v2 is the `v2` branch. Each deployed copy carries a `.sharet-version` file (v1 has none, which
means v1). Both run on the **same database**: v2 only adds tables, never alters or drops a v1 table, so v1 works
unchanged with v2's tables present, and v2's records stay untouched while v1 is live.

## Server layout

```
/home/sharet.africa/
  public_html/        the live version (scripts/deploy.sh deploys here)
  versions/v1/        a parked copy of v1
  versions/v2/        a parked copy of v2 (while v1 is live)
  backups/            database backups (each switch makes one)
```

## First time

1. While v1 is live: `scripts/switch-version.sh park` (keeps a copy of v1 in `versions/v1`).
2. Deploy v2 from the `v2` branch with `scripts/deploy.sh` as usual. v2 is now live.
3. `scripts/switch-version.sh park` again (keeps a copy of v2 in `versions/v2`).

## Switching

```bash
scripts/switch-version.sh status          # what is live and what is parked
scripts/switch-version.sh v1 --dry-run    # see the steps first
scripts/switch-version.sh v1              # v1 live for the audit
scripts/switch-version.sh v2              # v2 back afterwards
```

Each switch: backs up the database (with triggers), gives the incoming version the live `.env` and uploads, swaps the
folders, checks the main pages answer 200 and swaps straight back if any doesn't.

## Before switching to v1 while v2 moves money

v1 has no ledger, orders or rails. Before putting v1 live: switch off new orders in v2 (kill switch), let in-flight
orders finish or refund, and run `php artisan ledger:check`. While v1 is live, v2's scheduled jobs don't run; the first
thing after switching back is `ledger:check` and reconciliation of anything the rails did meanwhile.

## Keeping both deployable

- v1 fixes go on `main` and are merged into `v2`.
- Re-park after each deploy of a version (`park`), so the parked copy is current.
- A v2 migration that would change a v1 table needs a written decision first; it breaks the switch.
