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

## 7. Pricing for businesses

| Movement | Fee |
|---|---|
| Sharet ↔ Sharet (any two Sharet IDs, incl. splits and connected accounts) | **Free** |
| Collect by Sharet wallet | Free |
| Collect by M-PESA prompt | Collection cost passed through (to confirm with Cashia/Safaricom) |
| Pay out to M-PESA / Airtel / T-Kash | Standard send fee (config/pricing.php: Safaricom band + 20%, excise on our part) |
| Pay out to a bank (PesaLink) | KES 50 sample (to confirm) |

**Larger settlements are discounted** (owner, 7 Nov), for group companies and for any business with volume. Proposed
tiers on outside payout fees, per calendar month, **sample figures to confirm**:

| Monthly outside settlements | Discount on payout fees |
|---|---|
| Up to KES 1,000,000 | none |
| KES 1M – 10M | 25% |
| Over KES 10M | 40% |
| Sharet group companies | the next tier up from their volume |

The fee is always shown in the quote and the response before money moves.

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

Today: API keys per company (create, rotate, toggle, delete) and `GET /api/v1/me`; users have unique `handle`s;
Sharet ↔ Sharet transfers by @handle in the app; the mobile API (idempotent Sharet ↔ Sharet and M-PESA, see
MOBILE-API.md); the double-entry ledger with orders and journals.

Build order when approved:
1. Sharet IDs for users and companies (column, backfill, shown to businesses), and `accounts/resolve`.
2. Key scopes, sandbox/live separation, idempotency on `/v1`.
3. Quotes and payments (out), reusing the order engine; webhooks with signatures.
4. Payment requests (in) with the hosted pay page and in-app approval.
5. Split settlement in one journal; settlements (batch) with the second approver.
6. Connected accounts and consent; volume discount tiers.
7. Docs site from this file; sandbox test numbers.
