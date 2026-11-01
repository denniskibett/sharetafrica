# Where we left off (1 November 2026)

Live `sharet.africa` runs v2 (latest deploy `937cca4`) in test mode while `v2.sharet.africa` waits for DNS.
v1 is parked at `/home/sharet.africa/versions/v1`. Deploys: `scripts/deploy.sh` from this worktree (DEPLOY.md).

## To pick up first: real M-PESA payouts (send / withdraw)

Built, deployed, **switched off**. The tester sees "Sending to M-PESA and banks is not live yet" on purpose.

1. **You:** M-PESA Org Portal for `4339173`: create a production API initiator with B2C rights and generate its
   security credential. Enter both in live **Integrations → M-Pesa · Live → Settings** (initiator name, security credential).
2. **You:** in the portal move a small amount (e.g. KES 100) from the working account to the utility account.
3. **Us:** record it on live: `php artisan ledger:float 1110 1120 100 --note="portal transfer"`.
4. **Us:** set `RAIL_PAYOUT=daraja` in the live `.env` (tester only, through `RAIL_COLLECT_ONLY_FOR`), `config:cache`.
5. **Together:** send KES 10 from the tester to your own number; check the receipt, ledger (`ledger:check`) and the
   float hold captured. Then try a paybill and a bank.

Checked on 1 Nov: initiator and credential empty, utility float KES 0.00, payout rail off.

## Waiting on you

- **Scheduler cron** in CyberPanel for the sharet.africa site, every minute:
  `cd /home/sharet.africa/public_html && /usr/local/lsws/lsphp83/bin/php artisan schedule:run >> /dev/null 2>&1`
  (nightly backups, M-PESA status checks, payouts waiting for float, reconciliation, ledger check).
- **Email:** enter `noreply@sharet.africa` in live Integrations → Email (SMTP): host `sharet.africa`, port 587, TLS,
  the mailbox password. Add the two DNS records at Olitt (SPF with `ip4:185.113.249.146`, DKIM `default._domainkey`;
  values in the notes file `sharet-dns-email-records.md`). Then send one test email.
- **SMS:** enter the KenyaSMS API key (`ks_live_…`, `sms.send`) and sender ID in live Integrations → KenyaSMS, press Test.
- **DNS:** the `v2` A record for `v2.sharet.africa` (→ 185.113.249.146) at Olitt; then move v2 there and switch
  `sharet.africa` back to v1 (`scripts/switch-version.sh v1`).
- **Backups off the server:** CyberPanel remote backups (S3 / Backblaze / another server). Nightly dumps go to
  `/home/sharet.africa/.db-backups` once the cron runs.
- **Footer** for the `*.sharet.africa` sites: which footer, which sites.
- **Git remote:** none yet; give a repository URL to push `main` and `v2`.

## Test data to reset before real launch

Order 1 (KES 10 top-up, M-PESA `UK1PE00E4V`), order 2 (simulated send, returned to wallet), support case `CS-10001`,
the tester account `v2-tester@sharet.test` and its placeholder ID photos, and the live `.env` test switches
(`RAIL_SIMULATOR_IN_PRODUCTION`, `RAIL_COLLECT`, `RAIL_COLLECT_ONLY_FOR`). The ledger is insert-only: reset by restoring
the pre-test backup or with reversing entries, never by deleting rows.
