# Where we left off (7 November 2026)

Live `sharet.africa` runs v2 (latest deploy `7e861a2`, 7 Nov 22:42) in **test mode** while `v2.sharet.africa` waits
for DNS. v1 is parked at `/home/sharet.africa/versions/v1`. Deploys: `scripts/deploy.sh` from this folder (DEPLOY.md);
since 7 Nov they copy into `public_html` in place, so a server shell left open in it runs the new code.

## State of live money (7 Nov)

The ledger holds only real money. `ledger:check` passes: safeguarded KES 110, owed KES 100, buffer KES 10.

- Dennis (`kibettdennis@gmail.com`): order 5, KES 100 real M-PESA top-up. The only customer money.
- Tester (`v2-tester@sharet.test`): order 1, KES 10 real (M-PESA `UK1PE00E4V`), booked to **4800 Test payments
  kept** (Sharet's income) with `sharet:clean-simulated --keep-as-income=1`.
- Simulated orders 2, 3 and 4 were reversed (`sharet:clean-simulated`) and are hidden from customers.
- `RAIL_COLLECT_ONLY_FOR=v2-tester@sharet.test,kibettdennis@gmail.com`: those two get a real STK push. **Everyone
  else still tops up through the simulator (fake money).** Payouts are simulated for everybody.
- Still to tidy before launch: support case `CS-10001`, the tester account and its placeholder ID photos, and the
  test switches in the live `.env` (`RAIL_SIMULATOR_IN_PRODUCTION`, `RAIL_COLLECT`, `RAIL_COLLECT_ONLY_FOR`).
  The ledger is insert-only: reverse, never delete.

## To pick up first: real M-PESA payouts (send / withdraw)

Built, deployed, **switched off**.

1. **You:** M-PESA Org Portal for `4339173`: create a production API initiator with B2C rights and generate its
   security credential. Enter both in live **Integrations → M-Pesa · Live → Settings**.
2. **You:** in the portal move a small amount (e.g. KES 100) from the working account to the utility account.
3. **Us:** record it on live: `php artisan ledger:float 1110 1120 100 --note="portal transfer"`.
4. **Us:** set `RAIL_PAYOUT=daraja` in the live `.env` (only the accounts in `RAIL_COLLECT_ONLY_FOR`), `config:cache`.
5. **Together:** send KES 10 from Dennis's account to his own number; check the receipt, `ledger:check` and the float
   hold. Then try a paybill and a bank.

## Blocking real money (`php artisan sharet:golive` shows the live list)

- **Simulator off for everyone** (`RAIL_SIMULATOR_IN_PRODUCTION=false`, collections on M-PESA for all) once payouts work.
- **Written cover from a licensed partner** (Cashia / Gladys) **or CBK authorisation.**
- **Email:** live Integrations → Email (SMTP) for `noreply@sharet.africa` (host `sharet.africa`, 587, TLS); SPF
  (`ip4:185.113.249.146`) and DKIM `default._domainkey` at Olitt (notes file `sharet-dns-email-records.md`); one test email.
- **SMS:** KenyaSMS API key (`ks_live_…`, `sms.send`) and sender ID in live Integrations → KenyaSMS, press Test.
- **FX rate:** none recorded on live.
- **Backups off the server:** CyberPanel remote backups (S3 / Backblaze / another server).
- **Break-glass IPs:** `LOCKDOWN_ALLOW_IPS` is empty, so a lockdown can only be lifted from the server.

## Decisions waiting for you

- Approve `docs/v2/DECISIONS-FOR-APPROVAL.md`, including the sample tariffs and limits and the website claims.
- M-PESA prompt says "for account Sharet": use the person's Sharet ID (`SH-…`) instead? (`app/Integrations/Daraja/DarajaRail.php`)
- Selfie vs ID face match: an outside identity provider, or staff compare by eye (today).

## Verification (live since 7 Nov)

- ID photos are read with tesseract (`ID_READER=tesseract`); what the card says replaces typed details (old values
  kept). Backfill: `php artisan kyc:reread [--dry]` (also on Administration › Scheduled jobs).
- Verified only when the automatic checks pass (ID readable, name matches, 18+, ID not on another account) or staff
  approve in **Administration › Verifications**. People verified before the checks kept their level.
- One guided selfie takes the straight, left and right photos itself; **not yet tried on a real phone camera.**
- **Mobile app** not yet updated for the guided selfie or the "in review" state (bump version + `scripts/release-android.sh`).

## Infrastructure

- **DNS:** the `v2` A record for `v2.sharet.africa` (→ 185.113.249.146) at Olitt; then move v2 there and switch
  `sharet.africa` back to v1 (`scripts/switch-version.sh v1`).
- **Server:** 2 GB RAM, swap ~91%, Ubuntu 20.04 out of support: more RAM now, then a fresh 22.04/24.04 server.
- **Cloudflare:** configure trusted proxies first.
- **API follow-ons:** collection fees, an `api.` subdomain, queue workers so webhooks leave the request.
- **Offered, not started:** payout rail switch in the System screen instead of `.env`; a `scale` branch with a runbook.

## Done (for reference)

Scheduler cron (7 Nov) · test-money reset (7 Nov) · ID reading on live · verification checks and staff review ·
in-place deploys · API v1 and `/docs` · website from the canvas.
