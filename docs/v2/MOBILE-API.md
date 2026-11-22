# Sharet mobile API v1

Base: `https://sharet.africa/api/mobile/v1`. JSON in, JSON out. Every call sends `X-Device-Id` (a stable id the app
makes once and keeps). Signed-in calls also send `Authorization: Bearer <device token>`.

| Method | Path | Notes |
|---|---|---|
| GET | `config` | Public. API version, minimum app version, currency, test mode. |
| POST | `auth/login` | `identifier` (phone or email), `password`, `device_name`, `platform` → `201 {token, user}`. Old token for the same phone is revoked. 5 tries per identifier. |
| POST | `auth/logout` | Revokes this phone's token. |
| GET | `devices` / DELETE `devices/{id}` | Signed-in phones; revoke one. |
| GET | `me` | User, wallet balance, M-PESA number, handle, has PIN. |
| GET | `activity?page=` | Payments, newest first. |
| GET | `lookup?q=` | Find a Sharet member by @handle or phone → display name. |
| POST | `pin` | Set the 4-digit Sharet PIN (first time). |
| GET | `orders/{uuid}` | One payment and its status (poll after confirm). |

**Money calls** (need `Idempotency-Key`, 30 a minute, refused with 423 while the ledger is frozen):

| Method | Path | Body |
|---|---|---|
| POST | `send/sharet` | `to`, `amount`, `pin` |
| POST | `quote` | `phone`, `amount` → fee, excise, total, order uuid |
| POST | `orders/{uuid}/confirm` | `pin` |
| POST | `add-money` | `amount` (M-PESA prompt on the user's own phone) |

## Idempotency

- No key → `428`. Same key + same body → the first successful answer again, with `Idempotent-Replay: true`.
- Same key, different body → `409 idempotency_key_reused`; first call still running → `409 in_progress` (retry).
- Refusals (any 4xx/5xx) are not remembered, so fix the input (e.g. wrong PIN) and retry with the same key.
- The PIN is left out of the body comparison.

## Tokens

Stored hashed (`device_tokens`), tied to the device id, 90 days sliding, revoked on sign-out, on a new sign-in from
the same phone, or from the devices list. A blocked user's tokens stop working at once.

Tests: `tests/Feature/Mobile/MobileApiTest.php`. App: `~/mobile/sharet`.

## Android download (`/app/android`)

The APK is served from `/home/sharet.africa/downloads/android/` (config `mobile.android_dir`), outside the release
folders, so website deploys and rollbacks never touch it.

- `GET /app/android` downloads the 64-bit APK (most phones); `/app/android/armv7` downloads the build for older 32-bit phones.
- The homepage ("Get the Android app") and footer ("Android app") show the link only once an APK is uploaded.
- `GET /api/mobile/v1/config` returns `android: {version, build, url}`, so the app can tell people about updates.

**Every app update:** bump `version:` in `~/mobile/sharet/pubspec.yaml` (the `+N` build number must go up), then
run `~/mobile/sharet/scripts/release-android.sh`. It builds signed APKs, uploads them with `android.json`, keeps
the previous release and checks the live link. No website deploy is needed.

The release key is in `~/mobile/sharet-keys/` (never in git). **Back it up**: without it, no update can ever be
installed over the current app.

## Merchant and cashier (Phase 2)

**Owner** (signed in as usual, the shop's owner only; otherwise `403 no_shop`), prefix `merchant/`:

| Method | Path | Notes |
|---|---|---|
| GET | `merchant` | `shop` (null if none), today's sales / fees / net, balance, recent sales, refunds waiting, tills |
| POST | `merchant/setup` | `name`, `kra_pin` → creates the shop (verified personal accounts only); takes money once Sharet verifies it |
| GET | `merchant/get-paid` | Account number on Sharet's paybill and the counter QR payload |
| GET | `merchant/sales?day=&till=` | A day's sales and totals |
| GET | `merchant/sales/{uuid}` · POST `…/cancel` | One sale with its refunds |
| POST | `merchant/charges` | `amount`, `method` (`mpesa` + `phone` · `link` + `description` · `qr`) |
| GET | `merchant/refunds` | `waiting` and `done` |
| POST | `merchant/refunds/{uuid}/approve` | `pin` · POST `…/decline` |
| POST | `merchant/sales/{uuid}/refund` | `amount`, `reason`, `pin`: refund directly |
| GET/POST | `merchant/team` | Tills; add a cashier `name`, `phone` → `code` (also by SMS) |
| POST | `merchant/tills/{n}/{pause\|resume\|remove\|code\|close}` | |
| GET/POST | `merchant/settlement` | `mode` (`keep`/`bank`/`mpesa`), `bank`, `account`, `phone`, `settle_at`, `pin` |

**Cashier** (shop phone: `Authorization: Bearer <till token>` + `X-Device-Id`), prefix `cashier/`:

| Method | Path | Notes |
|---|---|---|
| POST | `cashier/link` | Public. `phone`, `code`, `pin`, `pin_confirmation`, `device_id` → `201 {token, till}` |
| GET | `cashier/me` | Shop, till, cashier, `open` (PIN entered this shift) |
| POST | `cashier/sign-in` · `sign-out` · `unlink` | `pin` opens the till (`401 till_locked` until then; `403 till_paused`) |
| POST | `cashier/charges` | `amount`, `method` (`mpesa` + `phone`, or `qr` → `pay_url` to show as a QR) |
| GET | `cashier/sales/{uuid}` | Poll while waiting: `pending` → `paid` / `failed` |
| POST | `cashier/sales/{uuid}/{cancel\|receipt\|refund}` | refund: `reason`, optional `amount` (asks the owner) |
| GET | `cashier/today` | This till's sales since its last close, refunds asked, today's totals |
| POST | `cashier/close` | End of day for this till; locks the phone |

Money calls (need `Idempotency-Key`): `merchant/charges`, `merchant/refunds/{uuid}/approve`, `merchant/sales/{uuid}/refund`, `cashier/charges`. Tests: `tests/Feature/Mobile/MerchantCashierTest.php`.
