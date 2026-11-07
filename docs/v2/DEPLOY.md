# Deploying sharet.africa

One server (CyberPanel, OpenLiteSpeed, PHP 8.3, MariaDB 10.3) at `185.113.249.146`. Deploys run from a developer's
machine with `scripts/deploy.sh`; there is no CI yet and no git remote. Each deploy is prepared in a new release folder,
then copied over `public_html` in place (a hard-linked snapshot of the old code is kept), so a bad release is one
command away from the previous one and `public_html` is never renamed: a shell left open in it runs the new code.

## Sites on the server

| Site | Folder | Linux user | Runs |
|---|---|---|---|
| `sharet.africa` | `/home/sharet.africa/public_html` | `share6158` | the live site (v1 on `main`, or v2 on `v2`) |
| `v2.sharet.africa` | `/home/v2.sharet.africa/public_html` | `vshar6292` | staging for v2 (created; waiting for DNS) |

Next to the live site: `releases/` (each deploy and the one it replaced), `backups/` (database and files per deploy),
`versions/` (parked copies of v1 and v2 for switching, see SWITCHING.md).

## The stages of one deploy

`scripts/deploy.sh` runs these in order and stops at the first failure. Nothing live changes before stage 5.

| Stage | What happens | If it fails |
|---|---|---|
| 1. Local checks | Clean git tree, test suite, `npm ci` and a fresh asset build | Nothing has touched the server |
| 2. Backup | On the server: database dump and an archive of the live folder into `backups/<timestamp>/` | Nothing live changed |
| 3. Upload | The repo (minus `scripts/deploy-exclude.txt`) and the built assets into a **new** `releases/<timestamp>/` | Delete the half-uploaded folder |
| 4. Prepare | Copies the live `.env`, `storage/` (uploads) and server-only public files into the release; `composer install --no-dev`; `APP_DEBUG=false`; **migrations**; caches | The live site still runs the old release. A migration that ran stays in the database: v2 migrations are additive, so the old code is unaffected |
| 5. Copy in | `public_html` is snapshotted to `releases/previous-<timestamp>` (hard links), then the release is rsynced over it in maintenance mode (`storage/` untouched); caches rebuilt; LiteSpeed restarted gracefully so OPcache reloads | The maintenance page lifts by itself; roll back with `--rollback` |
| 6. Check | The main pages must answer 200 | The script copies the snapshot straight back |

```bash
scripts/deploy.sh --dry-run     # see every step, change nothing
scripts/deploy.sh               # deploy the checked-out branch to live
scripts/deploy.sh --rollback    # put the previous release back (files only)
```

To restore the database too: `gunzip -c /home/sharet.africa/backups/<timestamp>/shar_main.sql.gz | mysql shar_main`
on the server. The ledger is insert-only, so prefer a forward fix (a reversal) over a restore whenever money moved.

### Deploying somewhere else (staging)

The same script, pointed at another site:

```bash
DEPLOY_SITE=/home/v2.sharet.africa DEPLOY_OWNER=vshar6292 DEPLOY_URL=https://v2.sharet.africa scripts/deploy.sh
```

Staging needs its own `.env` in its `public_html` first, with **its own database** (never the live one, so test money
never reaches the live ledger), `APP_ENV=staging` (allows the simulator), `MAIL_MAILER=log` (a copy of live data must not
email real customers) and no live provider keys.

## After a deploy that adds features

| When | Do once on the server |
|---|---|
| First v2 deploy on a database | `php artisan db:seed --class=Database\\Seeders\\LedgerAccountSeeder --force` (the chart of accounts) |
| First v2 deploy on a site | Cron for the site user: `* * * * * cd <site>/public_html && /usr/local/lsws/lsphp83/bin/php artisan schedule:run >> /dev/null 2>&1` (runs `ramp:resolve` every minute, `recon:run` daily, `ledger:check` hourly) |
| Receipts (PDF) | `storage/fonts` must exist and belong to the site user (the PDF font cache). The deploy copies `storage/` from live, so it carries over after the first time |
| New settings | Add them to the live `.env` before deploying (the release copies it); keep a dated copy of the old one |

## Backups and protection

- **Nightly database backup:** `php artisan db:backup` at 01:30 Nairobi (scheduler cron needed): a gzip dump with the
  ledger's triggers into `storage/app/backups/` (mode 600, kept 14 days). The password goes through a private options
  file, never the command line. Each deploy also dumps the database into `backups/<timestamp>/`.
- **Off the server:** both live on the same disk as the site. Copy them off nightly (CyberPanel's remote backups to S3 /
  Backblaze / another server), or a lost server loses its backups too.
- **Injection:** all queries go through Laravel's query builder with bound values; the few raw SQL fragments are fixed
  column names written in code, never user input. CSRF on every form, sessions on HTTPS.
- **The ledger defends itself:** postings and journals are insert-only (database triggers refuse UPDATE and DELETE), so
  even a compromised app account cannot rewrite money history; `ledger:check` hourly flags any imbalance.

## v2 on live in test mode (1 November 2026)

While `v2.sharet.africa` waits for DNS, v2 runs on `sharet.africa`:

- live `.env`: `RAIL_SIMULATOR_IN_PRODUCTION=true` (money is simulated and labelled Test mode), `ID_READER=none`
  (no Tesseract on the server)
- v1 parked at `/home/sharet.africa/versions/v1`; the `.env` from before v2 at `versions/env.before-v2-20261101-010618`
- ledger accounts seeded; tester account `v2-tester@sharet.test` (verified, placeholder TEST photos)
- **not done:** the scheduler cron above (needs adding in CyberPanel)

Once `v2.sharet.africa` resolves: issue its SSL, give it a database with a copy of live data and the staging `.env`,
deploy v2 there with the command above, then `scripts/switch-version.sh v1` puts the original site back on
`sharet.africa`.

## Before real money

Simulator off (`RAIL_SIMULATOR_IN_PRODUCTION` removed), a real rail adapter (Cashia or Daraja) with its keys in the
integrations side, the scheduler cron, a restore drill from `backups/`, `ledger:check` clean, Tesseract or a KYC provider
for ID reading, and the bands in `config/pricing.php` checked against the current Safaricom tariff.

## Known test failures

None: the deploy runs the Feature suite and everything passes (85 tests on `v2` at the time of writing).
