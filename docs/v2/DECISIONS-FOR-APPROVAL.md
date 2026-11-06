# Decisions to approve before real customers (7 Nov 2026)

Everything below was decided or built during the design and build sessions without a final yes from you.
Tick what you approve; for anything you don't, the "to change" column says where it lives.
Nothing here is live for real customers yet: sharet.africa runs v2 in **test mode** (simulated money,
real M-PESA collection only for the tester account).

## 1. Money rules

| ✓ | Decision | Now | To change |
|---|---|---|---|
| ☐ | **Strictly fiat.** No crypto, stablecoins or tokens anywhere (no VASP licence in Kenya). Phase 3 digital-asset journeys exist as **designs only**, marked "needs a VASP licence". | Enforced in code: only ISO currencies accepted | `config/fx.php` → `currencies` |
| ☐ | **FX source = Safaricom's M-PESA rate for the day**, recorded by treasury each morning (`php artisan fx:rate KES/USD <rate>`). No rate today → cross-currency payments pause (never a stale rate). | Built | `docs/v2/FX.md` |
| ☐ | **FX margin in Sharet's favour: 1.5%** on KES/UGX, KES/TZS, KES/RWF; **2%** on KES/USD. Payouts always round **down**. Quotes lock for 15 min. A >5% overnight move must be confirmed (typo guard). | Built | `.env` `FX_MARGIN_BPS`, `FX_MARGIN_BPS_USD` |
| ☐ | **Send fee = Safaricom's charge for the band + 20%**, excise 15% on our 20%; up to KES 100 free; adding money and Sharet-to-Sharet free. | In code since Nov 1 | `config/pricing.php` (check bands against Safaricom's current sheet) |
| ☐ | **Limits disagree between code and designs. Pick one:** code says basic KES 70,000 per payment and per day; standard KES 250,000 per payment / 500,000 per day. The designs (A03, A16, website) say basic KES 70,000 per payment / **150,000 per day**, bank grade **1,000,000 per day**. | Mismatch | `config/pricing.php` → `limits`, then I update the designs or the code |
| ☐ | **PIN lockout disagrees:** code locks wallet payments for **15 min** after 5 wrong PINs; designs and the website say **30 min**. Pick one. | Mismatch | `app/Support/TransactionPin.php` `LOCK_MINUTES` |
| ☐ | Bank paybill numbers for paying a bank through M-PESA (Equity 247247, KCB 522522, NCBA 880100, I&M 542542, Co-op 400200, Absa 303030, Stanbic 600100, Family 222111). | In code | `config/pricing.php` → `bank_paybills` (**verify each before live**) |

## 2. Security and identity

| ✓ | Decision | Now | To change |
|---|---|---|---|
| ☐ | **Strict sign-up details:** two names (letters only), a real email domain (DNS checked in production), and mobile numbers for Kenya (+254 7/1), Uganda (+256 7), Tanzania (+255 6/7), Rwanda (+250 72/73/78/79). Nobody can be saved with anything else. | Live (test mode) | `app/Support/Identity.php` |
| ☐ | **Two kill switches:** *ledger freeze* (no money moves, balances still show) and *system lockdown* (everyone out except super admins on an allowed IP). Super admin + password + reason, logged. | Live | System → Advanced; `php artisan sharet:lockdown` |
| ☐ | **Break-glass IPs are empty**, so a system lockdown can only be lifted from the server. Add your office/home IP? | Empty | `.env` `LOCKDOWN_ALLOW_IPS` |
| ☐ | **Staff money consoles** (reconciliation, ledger) ask for the password again every **15 minutes**; optional office-IP-only. | Live | `config/security.php` |
| ☐ | **Sign-up / KYC footprint:** IP, country, browser, languages, page and time for sign-up and each KYC step, encrypted, last 50 kept, visible to staff with people access. **Privacy notice updated: have a lawyer read it.** The keystroke ("keylog") audit was **not** built. | Live | `app/Support/Footprint.php`, `resources/views/privacy.blade.php` |
| ☐ | **Email code at sign-up** (6 digits, 15 min, 5 tries) is built but **off**. Switch on only after `php artisan sharet:mail-test you@gmail.com` lands in the inbox. | Off | `.env` `EMAIL_CODE_ON_SIGNUP=true` |

## 3. Look and feel

| ✓ | Decision | Now |
|---|---|---|
| ☐ | **One design system for everyone** (customers, merchants, businesses, staff): Revolut-like, light, white cards, pill buttons, compact sizes, colour by meaning (send blue, money in green, withdraw orange, pay violet, abroad teal, trade amber, lime = Sharet). | Live (customer home, money panel, activity, profile, staff theme) |
| ☐ | **Customer home:** ink header (logo; photo and bell; Habari; KES = USD rate), balance with hide eye, M-PESA number and @name as copy pills, outline Add / Send / Request / Withdraw on white. App starts **light**; dark is in the Account sheet. | Live |
| ☐ | Send arrow slants up, receive slants down. Paper-plane kept only for "send invite email" on the staff waiting list. | Live |
| ☐ | Role homes reuse the other wallet styles: merchant = lime card, business = ink + metal card, importer = card stack, supplier = pattern card. | Designs only |
| ☐ | The futuristic dark version is **not** used (kept on branch `backup/v2-futuristic-ui-2026-11-06`). | — |

## 4. Website (design ready, not yet coded)

| ✓ | Decision |
|---|---|
| ☐ | Revolut-style structure: huge headline "Money that moves the way Kenya does.", one idea per section, photo panels with real app UI, pricing table, safety section, closing band. |
| ☐ | **Six East African photos from Unsplash** (Kilifi, Nairobi ×2, Kampala ×2, Entebbe), contrast and colour boosted, credited in the footer. Unsplash licence: free for commercial use, no permission needed. Files and credits: `public/images/site/`. |
| ☐ | Placeholders you must fill or approve: **[LICENSED PARTNER]** (who holds customer money), **[NUMBER]** people, **[FEE]** / **[RATE]** in the pricing table, App Store / Google Play badges (no native app yet). |
| ☐ | Legal line in the footer: "Sharet Africa Ltd is not a bank. Payment services are provided with [LICENSED PARTNER], authorised by the Central Bank of Kenya…" (**lawyer to confirm wording**). |
| ☐ | Trade finance shown as "coming soon" with **Aimzone Ventures** named as funding partner. OK to name them publicly? |

## 5. Brands and logos

| ✓ | Decision |
|---|---|
| ☐ | **36 logos** of banks (your CBK list), M-PESA, Airtel Money, T-Kash, MTN MoMo, PesaLink, Visa, Mastercard, from each company's site or Wikimedia Commons; sources in `public/images/partners/partners.json`. Used to show where money goes (bank picker). They're trademarks: **check M-PESA/Safaricom, Visa and Mastercard brand rules before any marketing use.** |
| ☐ | 8 banks have no logo yet (Baroda, Bank of India, Ecobank, GTBank, Kingdom, M-Oriental, Premier, Victoria): their sites blocked download or had broken HTTPS. Get them from the banks or leave the bank icon. |

## 6. Infrastructure

| ✓ | Decision | Now |
|---|---|---|
| ☐ | v2 deployed to sharet.africa (test mode) on 7 Nov 01:20; backup `/home/sharet.africa/backups/20261107-012019`. | Live |
| ☐ | **v1 kept** at `versions/v1` (one-step switch: `scripts/switch-version.sh v1`) **and** a spare copy at `releases/v1`. | Done |
| ☐ | Commits pushed to GitHub `v2` (the docs commit about `releases/v1` and this file are local until you say push). | — |

## 7. Still blocking real money (not decisions, just to-dos)

Run `php artisan sharet:golive` on the server for the live list. As of today:
scheduler cron missing in CyberPanel · email (SMTP) not set (mail only logs) · SMS key not entered ·
M-PESA B2C initiator + credential missing · payout float 0 · FX rate not recorded on live ·
nightly backups not off the server · v2.sharet.africa DNS record · **written cover from a licensed partner
(Cashia / Gladys) or CBK authorisation** · reset test data (`php artisan sharet:reset-test-data`, report first).
