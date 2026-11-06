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
