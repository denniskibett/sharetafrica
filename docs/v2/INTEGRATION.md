# Integrating with Sharet (API v1, proposed)

Status: **agreed with the owner on 7 Nov 2026, not built yet.** This is the contract we build to. What exists in
code today is listed at the end. Public explainer page: canvas board *Website · Integrate* (Phase 1 · Website).

Who it's for: Sharet's own companies (scharge, Breve, Aimzone, aiyotickets, eServiz, Oteli, Shoope) and any outside
business that collects money from customers or settles money to people. Group companies use **the same public API**
as everyone else, so we build it once.

---

## 1. Three identifiers, three jobs

| | What it is | Example | Where it's used |
|---|---|---|---|
| **Sharet ID** | The fixed account number of a person or business. Never changes, never reused. | `SH-4K7Q2M` | Every settlement, webhook, statement and reconciliation. **The only identifier the API settles to.** |
| **@name** | Public alias, chosen by the account holder, can change. | `@mwangihardware` | What people see and type in the app. The API accepts it as input and always returns the Sharet ID it points to. |
| **API key** | Secret that lets a business's server act for its own account. | `sk_live_…`, `sk_test_…` | `Authorization: Bearer` header. Never the @name. |

Decisions (owner, 7 Nov):
- Customers see **only their @name** in the app for now. The Sharet ID appears for businesses: dashboard, API,
  statements and settlement reports.
- **Settlements are tied to the Sharet ID.** If an integration sends an @name, we resolve it to the Sharet ID at
  quote time, show the account's verified name, and lock the Sharet ID into the payment. Renaming an @name later
  never redirects money.

Format: `SH-` + 6 characters from `23456789ABCDEFGHJKMNPQRSTVWXYZ` (no 0/O/1/I/L/U), with a check character for
typos in later versions. One per user and one per business (company); a business's staff keep their own personal IDs.

## 2. Keys and safety

- Keys belong to the **business account** (company), created in Developers › API keys. Separate **sandbox**
  (`sk_test_`) and **live** (`sk_live_`) keys; sandbox money is simulated.
- **Scopes** per key: `read`, `collect`, `payout`, `connect`. A key only does what its scopes allow.
- **Payout keys**: live payout needs an IP allow-list, and batches above a threshold (KES 500,000 by default) need a
  second approver in the dashboard before money leaves.
- Every write takes an **`Idempotency-Key`** header: retries return the first result, never a second payment.
- Live keys only after the business passes **KYB** (registration, KRA PIN, directors, settlement account).
- No API call can take money **from a customer's wallet** without that customer approving it in the Sharet app
  (PIN) or paying by their own M-PESA prompt.
- Payment services are provided with Cashia (CBK-licensed PSP); customer money stays in the trust account.
- The ledger kill switch (freeze) stops every money call, as for the app.

## 3. Money in: collect

`POST /v1/payment-requests`. A business asks a customer to pay. The customer pays from their Sharet wallet (by @name or
phone, approved with their PIN in the app) or by an M-PESA prompt. Money lands in the business's Sharet wallet.

```http
POST /v1/payment-requests
Authorization: Bearer sk_test_…
Idempotency-Key: rent-2026-11-unit-4b

{
  "amount": "35000.00",
  "currency": "KES",
  "payer": { "handle": "@akinyi" },            // or { "phone": "0712 345 678" } or { "sharet_id": "SH-…" }
  "reference": "Rent Nov 2026 · Unit 4B",
  "channels": ["sharet", "mpesa"],
  "splits": [                                  // optional, see §5
    { "to": { "sharet_id": "SH-LANDL1" }, "percent": 90 },
    { "to": { "sharet_id": "SH-AGENT7" }, "percent": 8 },
    { "to": "self", "percent": 2 }
  ],
  "metadata": { "unit": "4B", "lease": "L-2041" }
}
```

Response `201`: `id`, `status` (`open`, `paid`, `expired`, `cancelled`), `pay_url` (a hosted page for people without
the app), `payer` (resolved name and Sharet ID), `expires_at`.

`GET /v1/payment-requests/{id}`. Status and, once paid, the M-PESA or Sharet reference.

## 4. Money out: pay and settle

`POST /v1/payments`: one payment. `POST /v1/settlements`: a batch (landlords, suppliers, staff).

```json
{ "amount": "31500.00", "currency": "KES",
  "to": { "sharet_id": "SH-LANDL1" },          // free, instant
  "reference": "Rent Nov 2026 · Unit 4B" }
```

Other destinations: `{ "type": "mpesa", "phone": "…" }`, `{ "type": "airtel", "phone": "…" }`,
`{ "type": "tkash", "phone": "…" }`, `{ "type": "bank", "bank": "equity", "account": "…" }` (PesaLink),
`{ "type": "paybill", … }`, `{ "type": "till", … }`. Every call returns the **fee and total before money moves**
(`POST /v1/quotes` gives the same without sending), and the recipient's verified name.

## 5. Split settlement

A payment request can carry `splits` (percent or fixed amounts that add up to the whole). When it's paid, the ledger
posts every leg in **one journal**: no leg can be paid without the others. Legs to Sharet IDs are free and instant.
Legs to M-PESA or banks are paid out as normal payments and charged (see §7). A failed outside leg is held in the
business's wallet with a `settlement.leg_failed` webhook, never lost.

## 6. Connected accounts (platforms like scharge and Breve)

A platform signs its own users up (landlords, agents, merchants) as **their own Sharet accounts**: verified, each with
a Sharet ID and @name. The platform never holds their money.

1. `POST /v1/connect/links`: returns a link where the landlord signs up or signs in and approves
   "Let scharge collect and settle for me" (scopes shown in plain words).
2. Webhook `account.connected` with their Sharet ID.
3. The platform acts for them with the header `Sharet-Account: SH-…`, only within the approved scopes. The
   landlord can disconnect in the app at any time.

Decision (owner, 7 Nov): connected accounts can **receive and send**. **Sharet ↔ Sharet is free**; sending
**outside Sharet** (M-PESA, Airtel, T-Kash, banks, SWIFT later) is charged at the normal rates.

## 7. Pricing: tariff tables (owner, 7 Nov)

Fees come from **two tables**, kept by staff in the dashboard (Staff › Pricing), versioned with an effective date. A
quote always uses the tariff in force when it's made, and records which version it used.

**Table 1 · Transaction tiers.** The fee for one movement, by **movement type** and **amount band**. Each row is one
band; each cell is a fixed fee, a percentage, or both (with an optional cap). Sample structure (figures to set):

| Amount band (KES) | Sharet ↔ Sharet | Collect by Sharet wallet | Collect by M-PESA prompt | Pay out to M-PESA / Airtel / T-Kash | Pay out to bank (PesaLink) |
|---|---|---|---|---|---|
| 1 – 100 | Free | Free | pass-through | Free | n/a |
| 101 – 1,000 | Free | Free | pass-through | band fee | KES 50 |
| 1,001 – 10,000 | Free | Free | pass-through | band fee | KES 50 |
| 10,001 – 100,000 | Free | Free | pass-through | band fee | KES 50 |
| 100,001 – 1,000,000 | Free | Free | pass-through | band fee | KES 100 |
| Over 1,000,000 | Free | Free | pass-through | band fee | 0.01%, cap KES 500 |

"Band fee" today is the personal tariff in `config/pricing.php` (Safaricom's charge for the band + 20%, excise on our
part); for businesses it becomes its own column in this table. Sharet ↔ Sharet stays free in every band.

**Table 2 · Volume tiers.** A discount on Table 1 fees for **outside** movements, by the business's **total outside
settlements in the calendar month so far**. Sample structure (figures to set):

| Monthly outside settlements (KES) | Discount on Table 1 fees |
|---|---|
| 0 – 1,000,000 | 0% |
| 1,000,001 – 10,000,000 | 25% |
| Over 10,000,000 | 40% |

Rules:
- The fee for a movement = Table 1 fee for its type and amount band, minus the Table 2 discount for the business's
  tier at that moment. A movement that crosses a tier boundary is priced at the tier it starts in.
- **Group companies** are priced one volume tier up from their actual volume (owner: discounted for larger
  settlements).
- Connected accounts are priced on their own volume, not the platform's.
- The fee and the tier used are returned in every quote and response, and printed on statements.

## 8. Webhooks

Register with `POST /v1/webhooks` (URL and events). Events: `payment_request.paid`, `payment_request.expired`,
`payment.paid`, `payment.failed`, `payment.refunded`, `settlement.completed`, `settlement.leg_failed`,
`account.connected`, `account.disconnected`. Every event names the **Sharet IDs** involved.

Signed: header `Sharet-Signature: t=<unix>,v1=<HMAC-SHA256 of "t.body" with the webhook secret>`. Retries with backoff
for 24 hours; delivery log in the dashboard. Treat webhooks as hints and confirm with `GET` when it matters.

## 9. Everything else

- `GET /v1/accounts/me`: your Sharet ID, @name, verification level, balances.
- `GET /v1/accounts/resolve?handle=@x`: Sharet ID, verified display name (masked), whether it can receive.
- `GET /v1/ledger`: entries by date, type, status; paginated; CSV export in the dashboard.
- Errors: `application/problem+json` (RFC 7807) with a plain-English `detail` and a stable `type`.
- Amounts are strings with two decimals; currency `KES` (TZS/UGX later). Fiat only.
- Rate limits: 60 requests/minute per key to start; payout batches up to 500 items.

## 10. Sandbox

Same endpoints, `sk_test_` keys, simulated money. Test numbers for M-PESA success, failure and timeout; test @names
(`@test-landlord`, `@test-agent`) that always exist; a button to fire any webhook.

## 11. Worked examples

**scharge, rent:** scharge connects each landlord and agent (§6) → creates a payment request per lease with splits
90/8/2 → the tenant pays from Sharet or M-PESA → the landlord and agent are paid instantly to their Sharet IDs (free),
scharge keeps 2% → landlords who want M-PESA or bank send it out themselves (charged), or scharge settles them in a
monthly batch (§4, discounted at volume).

**Breve, invoices:** Breve puts a "Pay with Sharet" link on each invoice (payment request with `pay_url`) → paid to
the merchant's own Sharet ID → Breve marks the invoice paid on `payment_request.paid`.

**Any shop:** a till or website checkout creates a payment request; the evening settlement to the bank is a single
`POST /v1/payments`.

## 12. What exists today, and the build order

**Built (7 Nov, branch v2, not yet deployed):**
- Sharet IDs (`SH-XXXXXX`) for every user and company, issued on creation and backfilled; never change.
- API keys with scopes (`read`, `collect`, `payout`, `connect`; every key can read). A key acts for the Sharet account
  of the person who created it, until business wallets get their own ledger accounts.
- `GET /api/v1/accounts/me`, `GET /api/v1/accounts/resolve` (masked name), `POST /api/v1/quotes`.
- `POST /api/v1/payments` (`payout` scope, `Idempotency-Key`, stopped by the ledger freeze) and `GET /api/v1/payments/{id}`.
  Sandbox keys simulate (test number 0700 000 002 always fails) and never touch the ledger. Live keys need a verified
  business (company `verified_at`) and go through the app's order engine: Sharet ↔ Sharet free and instant; outside
  payouts charged with the current personal tariff until the tariff tables exist.
- Webhooks: `GET/POST/DELETE /api/v1/webhooks` (public HTTPS only), events `payment.paid`, `payment.failed`,
  `payment.refunded`, signed `Sharet-Signature: t=…,v1=…`, sent after commit, retried for 24 hours by
  `php artisan api:webhooks-retry` (scheduled every minute: **needs the server cron**).
- Errors as `application/problem+json`.
- Payment requests (money in): `POST /api/v1/payment-requests` (`collect` scope, `Idempotency-Key`), `GET …/{id}`,
  `POST …/{id}/cancel`. Hosted pay page `/r/{token}`: Sharet wallet (signed in + PIN, free, only the named payer if one
  is set) or an M-PESA prompt to any phone (lands in the business's wallet). Sandbox requests show "Simulate paid /
  cancel" and never touch the ledger. Webhooks `payment_request.paid`, `payment_request.expired`; expiry by
  `php artisan api:requests-expire` (scheduled every minute).
- Split settlement on payment requests: legs to Sharet IDs or @names (resolved to Sharet IDs at creation), by percent or
  amount, whole shillings, the rest stays with the business ("self"). Paid all-or-nothing in one database transaction
  from the business's wallet once the request is paid; on failure the money stays with the business
  (`settlement.failed`) and `POST /api/v1/payment-requests/{id}/settle` retries. Legs to M-PESA/banks are refused:
  recipients send outside themselves (charged).
- Batch settlements: `POST /api/v1/settlements` (`payout`, `Idempotency-Key`, up to 500 items, every item checked
  before anything moves), `GET /api/v1/settlements/{id}`. Live batches over KES 500,000 (`API_SECOND_APPROVAL_CENTS`)
  return 202 `awaiting_approval` and wait in Dashboard › **API approvals** for a different person from the same business
  with API-key access, after re-entering their password; the key's owner can't approve. Webhooks
  `settlement.awaiting_approval`, `settlement.completed`, `settlement.failed`.

- Business wallets: every company has its own Sharet account (its Sharet ID, an @name from its name, e.g. `@scharge`),
  created on first use and held as an account that can never sign in (web or app). API keys that belong to a company
  act for it; personal keys act for their owner. Verified companies get business limits (sample: KES 1,000,000 per
  payment, 10,000,000 a day; confirm), unverified ones can't move money. Dashboard › API approvals shows the wallet.

- Connected accounts: `POST /api/v1/connect/links` (`connect` scope; scopes `collect`/`payout`, optional
  `reference` and https `return_url`), `GET /api/v1/connect/accounts`, `POST /api/v1/connect/accounts/{id}/disconnect`.
  The person opens `/connect/{token}` (7 days), signs in, reads what the platform may do, and approves with their PIN
  or declines; they disconnect any time in Account › **Connected apps**. The platform acts for them with
  `Sharet-Account: SH-…`, only within the approved scopes and their own limits (receive and send: free inside Sharet,
  charged outside). Webhooks `account.connected`, `account.disconnected`.

- Tariff tables (§7): `tariffs` versions (Table 1 bands per movement: fixed + percent, capped; Table 2 volume tiers;
  excise on the fee), edited in Staff › **Pricing** (admin.system + fresh password; saving adds a version with an
  effective date). API quotes, payouts and batch items are priced from the version in force, on the paying account's
  own outside volume this month (Sharet companies one tier up, marked on the same screen); every API payment records
  the version and discount. Sharet ↔ Sharet is always free. Version 1 holds the samples below. Collection fees
  (`collect_mpesa`) are in the table but not charged yet.

- IP allow-list: live keys with the `payout` scope move money only from listed addresses (single IPs or ranges, /8 or
  narrower, up to 20), set when the key is made or later under Access in Developers › API keys; without a list they get
  `ip_allow_list_required`, from elsewhere `ip_not_allowed` (with the IP we saw). Sandbox keys and reading need no list.
  The API keys screen shows each key's scopes and addresses and the business's Sharet ID. **If Cloudflare or another
  proxy is ever put in front of sharet.africa, configure trusted proxies first**, or every caller will look like the proxy.

**Still to build, in order:**
1. Docs site from this file.
