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

## 0b. API and settlements (you decided, 7 Nov)

The integration contract is `docs/v2/INTEGRATION.md`; the public explainer is the canvas board *Website · Integrate with Sharet*. Approved; not built yet.

| ✓ | Decision |
|---|---|
| ✓ | **Settlements are tied to the Sharet ID** (`SH-` + 6 characters, fixed per person and per business). The API accepts an @name but always resolves and locks the Sharet ID. |
| ✓ | **Customers see only their @name** in the app for now; the Sharet ID is shown to businesses (dashboard, API, statements). |
| ✓ | **Connected accounts** (e.g. scharge landlords, Breve merchants) can **receive and send**: free between Sharet IDs, **charged outside Sharet** at normal rates. |
| ✓ | **Group companies use the same public API** as outside businesses and get **discounted fees on larger settlements**. |
| ✓ | **Tariffs are tables:** Table 1 = fee by movement type and transaction amount band; Table 2 = discount by monthly volume tier. Kept by staff (Staff › Pricing), versioned by effective date. |
| ☐ | Set the figures in **Staff › Pricing** (version 1 = samples: mobile payouts = Safaricom band + 20%; bank KES 50 / 100 / 0.01% max 500; volume 25% over KES 1M, 40% over 10M; excise 15% on the whole fee: **check excise with a tax adviser**). Samples were in INTEGRATION.md §7: bank KES 50/100/0.01% cap 500; volume 0% / 25% over KES 1M / 40% over KES 10M; group companies one tier up). |
| ☐ | Batches over KES 500,000 need a second approver; payout keys need an IP allow-list; live keys only after business verification (KYB). |
| ☐ | **Business wallet limits** (sample): KES 1,000,000 per payment and 10,000,000 a day for verified businesses (`config/pricing.php` → `limits.business`). Confirm. |
| ☐ | Collection by M-PESA prompt: cost passed through. Confirm the rate with Cashia/Safaricom. |

## 0c. Cloud9 as the payment rail (20 Nov)

| ✓ | Decision | Now | To change |
|---|---|---|---|
| ☐ | **Cloud9 is invisible to customers.** Sharet is the only name in the app, receipts, prompts and the website; Cloud9 appears only on staff screens. | Built | `docs/v2/RAILS.md` |
| ✓ | **The customer pays Cloud9's charge on a top-up** (you decided, 20 Nov): pay KES 100, KES 98.50 reaches the wallet, the KES 1.50 shows as a payment charge. On sends and payouts the charge comes out of the Sharet fee already shown. | Built | `app/Ramp/RampOrders.php` (chargeToCustomer) |
| ☐ | **Website footer and About page no longer name Cashia or a trust account:** "Payments are carried by licensed payment partners in Kenya, and customer money is kept apart from Sharet's own money." Have a lawyer read it; add the licensed partner's name if required. | Built, not deployed | `resources/views/site/partials/footer.blade.php` and the pages |
| ✓ | **Cloud9 is the default rail for every payment once its key is entered** (you decided, 21 Nov); the M-PESA paybill takes over only in downtime (Cloud9 refuses). A route approved on Payment rails still wins; `RAIL_PREFER_CLOUD9=false` turns it off. Money out falls back to the paybill only when it has the B2C initiator and float. | Built | `app/Integrations/Rails.php` |
| ✓ | **Money in Cloud9 can't take goes to Sharet's own M-PESA paybill** (you decided, 21 Nov): an M-PESA prompt Cloud9 refuses before accepting is sent from the paybill instead. Not card top-ups (M-PESA has no card); never after a timeout. | Built | `config/rails.php` `collect_fallback` |
| ☐ | **Two people to switch a rail**, and fallback only when a rail refuses before accepting (never after a timeout). | Built | System › Payment rails |

## 0e. Revenue share and markup (you decided, 22 Nov)

| ✓ | Decision | Built | Where |
|---|---|---|---|
| ✓ | Every money movement is priced as the partner's own charge (Cloud9's, or Safaricom's on our paybill) **plus Sharet's markup of 0.5%, at least KES 1**, nothing up to KES 100. Sharet keeps the markup (4100), excise (15%) set aside from it (2300). | Yes | `config/pricing.php` `sharet_markup`, `app/Ramp/Markup.php` |
| ✓ | Money in: the markup comes off what arrives (with the partner's charge). Money out: added to the fee shown before the PIN. Card top-ups: card charge on top, markup off the amount. | Yes | |
| ✓ | Businesses: the same markup less their volume discount; a business tariff never prices below partner charge + markup. | Yes | `app/Api/Tariffs.php` |
| ✓ | Sharet ↔ Sharet stays free. | Yes | |
| ☐ | **No cap on the 0.5%**: KES 50,000 out now costs a KES 358 fee (was 129.60). Cap it (e.g. KES 200)? | Uncapped | `sharet_markup` |
| ☐ | Website price table now says "Network charge + 0.5%" for adding money and "Bank's charge + 0.5%" for banks; it still says "Send to M-Pesa or Airtel" though Cloud9 pays M-PESA only. | Edited | `index`, `personal` |

Revenue share screen: Administration › Revenue share (who kept what, by rail, kind and payment).

## 0d. AML screening (you asked, 22 Nov: "before anything hits Cloud9")

Every payment is screened when it is confirmed, before any money moves (an M-PESA prompt, a payout through Cloud9 or the paybill, a Sharet-to-Sharet transfer). A rule either **holds** it for a person (Compliance › AML checks: approve → it carries on; stop → nothing moved; never your own payment; fresh password; logged) or **blocks** it. Figures in `config/aml.php`; please set them.

| ✓ | Rule | Starting figure | Result |
|---|---|---|---|
| ☐ | Watch list: a phone, paybill, till, account or Sharet ID staff listed | | Block |
| ☐ | Payments in an hour (any kind) | more than 10 | Hold |
| ☐ | Money out in an hour | more than KES 150,000 | Hold |
| ☐ | Structuring: payments at 90%+ of the per-payment limit | the 3rd in 24 hours | Hold |
| ☐ | First payment to someone new | KES 20,000+ | Hold |
| ☐ | Account under 24 hours old paying | KES 10,000+ | Hold |
| ☐ | Money in and straight out: KES 10,000+ in during the hour, 90% of it out to 3+ people | | Hold |
| ☐ | 10+ different people paid the wallet in a day, then money out (mule) | | Hold |
| ☐ | Politically exposed person paying | KES 50,000+ | Hold |

Not screened: paying a verified business or a business's API request skips the new-payee and new-account rules; a verified business's own wallet (payroll, settlements, API payouts) is checked against the watch list only (its limits and two-person approvals apply). Money coming in by account number or card can't be refused when it arrives: it is credited, and anything odd shows on the next payment out.

## 1. Money rules

| ✓ | Decision | Now | To change |
|---|---|---|---|
| ☐ | **Strictly fiat.** No crypto, stablecoins or tokens anywhere (no VASP licence in Kenya). Phase 3 digital-asset journeys exist as **designs only**, marked "needs a VASP licence". | Enforced in code: only ISO currencies accepted | `config/fx.php` → `currencies` |
| ☐ | **FX source = Safaricom's M-PESA rate for the day**, recorded by treasury each morning (`php artisan fx:rate KES/USD <rate>`). No rate today → cross-currency payments pause (never a stale rate). | Built | `docs/v2/FX.md` |
| ☑ | **FX markup in Sharet's favour (owner, 22 Nov 2026): dollars bought at the reference + KES 1.30 and sold at the reference − KES 3.00; UGX, TZS and RWF bought at + 1.5% and sold at − 3.5%.** KES/USD reference from the Central Bank of Kenya daily (`fx:cbk`). Payouts always round **down**. Quotes lock for 15 min. A >5% overnight move must be confirmed (typo guard). | Built | `.env` `FX_USD_BUY_KES`, `FX_USD_SELL_KES`, `FX_BUY_MARKUP_BPS`, `FX_SELL_MARKDOWN_BPS` |
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
| ☐ | **The ID decides (you asked, 7 Nov):** the uploaded ID is read (tesseract OCR) for name, ID number, birth date, sex, nationality, and the places on the card. County = the district (or district of birth) when it is one of the 47 counties; town = sub-location, else location, division or place of issue. What the card says **replaces** what the person typed; the old value is kept in their encrypted KYC record under `replaced`. `kyc:reread` backfills IDs already uploaded (also on Administration › Scheduled jobs). OCR on phone photos is imperfect: staff should still eyeball the details. | Built, not deployed; live `ID_READER=none` | `app/Support/Kyc.php`, `app/Integrations/IdReader/KenyanIdText.php` |
| ☐ | **Unfreeze needs a selfie (22 Nov):** after *Freeze my account*, unfreezing needs the PIN **and** a fresh selfie, so a thief with the phone and the PIN can't undo it. There is **no automatic face matching** in Sharet, so the selfie waits in **Administration › Verifications › Unfreeze**, where a person compares it with the verification selfie and approves (unfreezes) or rejects with a reason the customer sees. Freezing again cancels a waiting request. **The Flutter app must send the selfie** (`selfie`: camera data URL or image file) to `POST /api/mobile/v1/me/unfreeze`; it answers 202 and stays frozen until staff approve. Add automatic face matching later? | Built (staging) | `app/Support/Freeze.php` |
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

The homepage `/` is now the canvas design (generated: `resources/views/index.blade.php`, `public/css/site.css`, layout `resources/views/site/layout.blade.php`). The hero rate is **today's real rate**, or "Today's rate, before you pay" if treasury hasn't recorded one. Footer links with no page yet (Careers, Press, Terms) are left out. **Personal, Business (/merchants), Developers, Company, Contact, Trade, Privacy and Blog are now built in code from the canvas boards Web*V2** (7 Nov, not yet deployed). Their real forms (business waiting list, sandbox request, trade application, contact), the team and group companies on Company, real blog posts and the Android test build on Developers are kept; same footer as the home page everywhere. Points to confirm in them:

| ✓ | Decision |
|---|---|
| ☐ | Old claims dropped because they don't match the Kenya pilot: "~60% cheaper", Tanzania/Uganda sending live today, "CBK licence application submitted Q1 2026", "client funds at Equity Bank", "MTN Uganda live", "privately held, no outside investment". Tell me which of these are true and should come back. |
| ☐ | **Business** and **Trade finance** pages say "coming soon" with a business waiting list; settlement "every evening at 17:00" and trade terms (3.5% / 60 days, 0.1% a day late, two approvers above KES 500,000) are samples. |
| ☐ | **Developers:** "sandbox on request" and six v1 endpoints (payments, quotes, balance, ledger, webhooks) — this is the proposed public API; only the mobile API exists in code today. |
| ☐ | **Contact:** support@sharet.africa for customers and fraud, hello@sharet.africa for business, partners and press; "reply within one working day". |
| ☐ | **Privacy:** your current notice word for word, in a new layout with a contents list and a short "In short" summary at the top (new text: please check it). |
| ☐ | **Blog:** the three posts shown are samples; the real page lists your published posts. |

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
email (SMTP) not set (mail only logs) · SMS key not entered ·
M-PESA B2C initiator + credential missing · payout float 0 · FX rate not recorded on live ·
nightly backups not off the server · v2.sharet.africa DNS record · **written cover from a licensed partner
(Cashia / Gladys) or CBK authorisation**. Simulator still on for everyone not in `RAIL_COLLECT_ONLY_FOR`. (Cron and test-money reset done 7 Nov.)

## 8. Merchant and cashier (Phase 2, built 22 Nov: server on `claude/merchant-app-5d3e0e`, app on `claude/merchant-app`; test mode only)

Built from the canvas page "Phase 2 · Mobile app · Merchant and cashier". Every figure below is a proposal in
`config/merchant.php` (changeable without code). Nothing here is live until you say "go live".

| ✓ | # | Decision | Proposed now |
|---|---|---|---|
| ☐ | M1 | **Merchant fee** per sale, M-PESA prompt / QR / link paid by M-PESA, and money paid to the shop's account number | **1.5%** of the sale, excise inside it (the design's "[MERCHANT RATE]"). Taken from the shop's wallet when the sale is paid, its own ledger journal (4110 + 2300) |
| ☐ | M2 | Fee when the customer pays from a **Sharet wallet** (QR or link) | **Free** for the shop (0%), to push Sharet wallets. Say if it should be the same 1.5% |
| ☐ | M3 | **Card** payment links ("[CARD RATE]", Cloud9 3.5% underneath) | **Not built yet**: links take M-PESA and Sharet wallet. Need the card rate and who pays the card charge by default |
| ☐ | M4 | **Cashier limit** per sale | **KES 150,000** a sale; the owner charges up to the shop's own limits. No daily cap per cashier yet; want one? |
| ☐ | M5 | **Refund rules** | Cashier asks (own till's sales, up to **30 days** old); only the **owner approves, with their Sharet PIN**; partial refunds allowed, never more than the sale. Back the way it came: Sharet wallet at once (free), M-PESA payout with **the payout fee paid by the shop**. Managers who can approve: not built (owner only) |
| ☐ | M6 | Is Sharet's **fee returned** when a sale is refunded in full? | **No** (kept). Switch: `MERCHANT_REFUND_RETURNS_FEE=true` |
| ☐ | M7 | **End of day and settlement** | Tills close automatically at the shop's time (default **17:00**, the owner can change it) and a cashier can close their own till earlier. **Automatic settlement is OFF** (`MERCHANT_AUTO_SETTLE`): when on, the whole wallet balance goes daily to the owner's chosen bank (through its paybill) or M-PESA number, the payout fee from the balance. "[SETTLEMENT FEE]" per day: none beyond the normal payout fee. Changing where money settles needs the owner's PIN |
| ☐ | M8 | **Who can open a shop** | A verified personal account holder (basic KYC); the shop takes money only after Sharet verifies the business (KYB, `verified_at`), as on the web |
| ☐ | M9 | **Cashier sign-in** | Owner adds a cashier by phone → an invite code by SMS (valid 48 h, also shown to the owner) → on the shop phone the cashier enters phone + code and picks a 4-digit cashier PIN. Each shift opens with the PIN (14 h); 5 wrong PINs lock the till 15 min; pausing a till stops it at once |
| ☐ | M10 | **Shop QR for the counter** | Encodes the shop's paybill + account number (pay from the M-PESA menu). A QR where the customer types the amount on a Sharet page is not built; the cashier's QR carries the amount |
| ☐ | M11 | Owner web screens | None in the design, so none built: the owner uses the app. Staff see shops in Administration › Businesses as before |
