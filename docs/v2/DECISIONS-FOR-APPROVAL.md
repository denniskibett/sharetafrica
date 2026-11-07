# Decisions to approve before real customers (7 Nov 2026)

Everything below was decided or built during the design and build sessions without a final yes from you.
Tick what you approve; for anything you don't, the "to change" column says where it lives.
Nothing here is live for real customers yet: sharet.africa runs v2 in **test mode** (simulated money,
real M-PESA collection only for the tester account).

## 0. Rollout order (you decided, 7 Nov)

**Kenya pilot first, Tanzania next, Uganda after.** The website and the app designs now say so: Tanzania shows
"next" (Vodacom M-Pesa, Airtel Money, Mixx by Yas, banks), Uganda "later". Code still lists UGX/TZS/RWF as
offered FX pairs; cross-border sending is not switched on anywhere, so nothing is promised early.

| ✓ | Website decision |
|---|---|
| ☐ | Headline: **"Every wallet. Every border. One app."** with wallet-to-wallet and cross-border as the two lead sections. |
| ☐ | **Wallet to wallet:** M-PESA, Airtel Money, T-Kash and PesaLink logos with "Sharet ↔ Sharet free", "M-Pesa ↔ Airtel ↔ T-Kash" and "any wallet ↔ any bank". Uses those brands' logos: **check each brand's rules** before launch. |
| ☐ | **Across borders:** Kenya pilot now → Tanzania next → Uganda after, sample KES 10,000 → TZS 202,909 (sample rate 1 KES = TZS 20.29), fee KES 50, "Join the cross-border waitlist". |
| ☐ | **Brand presence:** a plain dark closing band, "Join Sharet Kenya. Tanzania next." (your wording). The big footer wordmark was tried and removed at your request. The Sharet-mark pattern stays only on the supplier card (wallet style 4). |
| ☐ | **Motion:** the hero is four photo scenes (Kilifi, Nairobi, Tanzania next, everyday) that cross-fade with a slow zoom and change the money cards with them; no arrows or dots. Wallet cards glide sideways; app phones swap screens; business chart bars grow. All off for reduced-motion users. |
| ☐ | **Wallets drawn as debit-style cards** (M-PESA, Airtel Money, T-Kash, PesaLink, Sharet) to show they're linked in one app. These are illustrations, not cards we issue, but they use those brands' logos and colours: **check each brand's rules**, or switch to plain tiles. |
| ☐ | **"Your money, on your phone" section (customer-centric):** one phone that plays through home → Send to Tanzania (recipient, amount, delivered) → paying a till, with floating cards (money from Akinyi, Tanzania rate, Naivas payment). Placed above the safety section, which now sits above "Everything you pay for". Sample customer on the website is **Juma Jones**. |
| ☐ | **App Store and Google Play badges** drawn with their marks. Before launch use Apple's and Google's official badge artwork, and only once the apps are live in the stores. |

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

## 3b. App screens built in code (7 Nov, not yet deployed)

| ✓ | Decision |
|---|---|
| ☐ | **Sign in, sign up and the other auth pages** moved to the new look (A01/A02). Same fields, routes and checks. Sign in stays **email + password**: the designed phone-and-PIN sign in, SMS code and KYC camera steps are not built yet. |
| ☐ | Sign up labels the phone field "M-PESA number" ("Your mobile number becomes your wallet number"); no fixed +254 because UG/TZ/RW numbers are also accepted. Desktop shows the A01 welcome card beside the form. |
| ☐ | **Onboarding** restyled; lane colours: individual blue, merchant violet, business amber, developer ink. |
| ☐ | **Help** page (/help): search, common questions, fraud steps. Answers use the live settings (fees, limits KES 70,000 verified; 250,000 per payment / 500,000 per day bank grade). **Check the wording**, especially "Until then you can receive, but not send". Report fraud opens an email to support@sharet.africa. Chat, "My cases" and one-tap freeze are not built (no backend). |
| ☐ | **Coming-soon previews** for Requests, Card, Send abroad (Tanzania next, Uganda later) and SWIFT: design look with sample details (labelled as a preview), a disabled "Tell me when it's ready" button, nothing that moves money. Reached from the Account menu under "Soon". |

## 4. Website (homepage coded 7 Nov from the approved board; not yet deployed)

The homepage `/` is now the canvas design (generated: `resources/views/index.blade.php`, `public/css/site.css`, layout `resources/views/site/layout.blade.php`). The hero rate is **today's real rate**, or "Today's rate, before you pay" if treasury hasn't recorded one. Footer links with no page yet (Careers, Press, Terms) are left out. **Other public pages (Personal, Merchants, Developers, Company, Contact, Trade, Privacy, Blog) still use the old look**: they need designs first.

| ✓ | Decision |
|---|---|
| ☐ | Revolut-style structure: huge headline "Money that moves the way Kenya does.", one idea per section, photo panels with real app UI, pricing table, safety section, closing band. |
| ☐ | **Six East African photos from Unsplash** (Kilifi, Nairobi ×2, Kampala ×2, Entebbe), contrast and colour boosted (2000px JPEG + 1200px WebP for the web). Unsplash licence: free for commercial use, credit not required, so **no visible credits**; photographer names are kept hidden in a code comment and in `public/images/site/credits.json`. |
| ☐ | **Cashia named as the licensed partner:** "Payment services are provided with Cashia (Kashia Services Limited), licensed by the Central Bank of Kenya as a payment service provider", with a "Check on CBK's licensed directory" link. Kashia is on CBK's directory, but **Cashia must agree in writing before this goes on the live site.** |
| ☐ | **No CBK logo.** A regulator's logo on our site reads as CBK endorsing Sharet; CBK doesn't allow that kind of use. We name the licensed partner in text and link to CBK's directory instead. Ask Cashia/your lawyer if you want anything stronger. |
| ☐ | No made-up user count: the closing band says "Join Sharet Kenya. Tanzania next." Put a real number there once you have one you can stand behind. |
| ☐ | Legal line in the footer: "Sharet Africa Ltd is not a bank…" (**lawyer to confirm wording**). |
| ☐ | Trade finance shown as "coming soon" with **Aimzone Ventures** named as funding partner. OK to name them publicly? |

**Sample figures now in the designs (placeholders replaced; confirm or give me the real ones):**
FX from a sample Safaricom rate of KES/UGX 28.65 and KES/USD 0.00775 → customer 1 KES = UGX 28.22, USD 0.0076 (KES 131.67 per USD) ·
send to M-Pesa "from KES 9, free up to 100" (from `config/pricing.php`) · withdraw to bank by PesaLink **KES 50** ·
send to Uganda **KES 50** + rate · SWIFT **KES 2,500** · card top-up fee **KES 150** on KES 5,000 ·
Uganda cash-out **UGX 1,500** · trade finance **3.5% for 60 days**, late fee **0.1% a day**, two approvers above **KES 500,000** ·
supplier early payment **1.8%** · lender on-time rate 96% · KPLC ~33 kWh per KES 1,000 · digital-asset price KES 130.21 (Phase 3 sample) ·
Sharet's bank account for transfers shown as "Shown in your app" (no real I&M account number yet) · references (M-PESA, PesaLink, MTN) are format samples.

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
