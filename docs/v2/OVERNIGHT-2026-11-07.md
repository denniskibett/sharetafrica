# Overnight log: Sharet mobile app (7 Nov 2026)

You said: build a Flutter app in ~/mobile/sharet, test it against the live API, keep it light, make the API easy
and safe (no lost data), fingerprint / device sign-in, payments Sharet→Sharet and Sharet→M-PESA. You went to bed
and asked me to decide and note every decision here. Newest at the bottom.

## Decisions

1. **One small mobile API, versioned: `/api/mobile/v1`** (routes/api.php, `App\Http\Controllers\Mobile\MobileController`).
   It is a thin layer over the same code the website uses (RampOrders, TransactionPin, ledger, limits, kill switches),
   so there is one set of money rules. *Why:* nothing to keep in sync; easy to manage.
2. **Sign-in = phone or email + password once per phone → a device token.** Stored hashed in `device_tokens`, tied to
   the phone's id (`X-Device-Id`), 90 days sliding, revocable (sign-out, or the devices list). One live token per
   phone. *Why:* no extra package (no Sanctum); fingerprint unlock happens on the phone (below).
3. **Fingerprint / Face ID unlocks the token on the phone** (iOS Keychain / Android Keystore via flutter_secure_storage +
   local_auth). Payments still need the 4-digit **Sharet PIN**, checked by the server. *Why:* biometrics prove the
   phone's owner is holding it; the PIN keeps money approval server-side and identical to the website.
4. **Every money call needs an `Idempotency-Key`** (`App\Http\Middleware\Idempotent`, table `mobile_requests`).
   A retry with the same key gets the first answer back and never runs twice; refusals (wrong PIN, frozen ledger,
   not enough money) are not remembered, so the app can fix and retry with the same key. *Why:* the existing
   Sharet→Sharet transfer was not safe to repeat; a dropped connection could have paid twice. This is the
   "don't lose data" piece on the server side.
5. **Sharet→M-PESA is two steps: quote (fee + total shown) then confirm with the PIN**; the app polls the order.
   Sharet→Sharet is one step (free). Add money = M-PESA prompt on the user's own phone.
6. **Sign-up stays on the website for now** (the app links to it). *Why:* KYC photos, selfies and the email code are
   already built there; duplicating them in the app tonight would be rushed. Next step for the app.
7. Tests: `tests/Feature/Mobile/MobileApiTest.php` (sign-in tied to the phone, no double payment on retry, wrong PIN
   then corrected on the same key, M-PESA quote→confirm→paid, frozen ledger refuses and forgets the key).
8. **The app keeps an outbox only for PIN-approved payments** (saved without the PIN, in the phone's secure storage).
   On the next launch each one is asked again with its key: it either returns what already happened, or is refused
   (no PIN), which means nothing moved. Quotes and Add money are never replayed, so an old M-PESA prompt can never
   pop up hours later. The app retries a payment up to 4 times with the same key on timeouts and server errors.
9. **App lives in `~/mobile/sharet`** (Flutter, iOS + Android, id `africa.sharet.app`). Three packages only
   (http, flutter_secure_storage, local_auth) to keep it light.
10. **Look:** the calm ink home from the canvas (Habari, rate line, balance with hide/show, copy chips for the
    M-PESA number and @handle, Add / Send / Request / Withdraw, recent list). Compact density, text at 94%,
    46px buttons, native page transitions (Cupertino on iOS, fade on Android) and short cross-fades only; no
    heavy animation. Send arrows slant up, received slant down.
11. **Brand kept from the files:** the logo PNGs are rendered from `public/images/brand/*.svg`; fonts are the
    website's own files (Inter Tight for text, Outfit for numbers, tabular). App icon: the Sharet mark on lime.
12. **Testing:** a Flutter integration test (`integration_test/app_test.dart`) drives the real app on the iPhone
    simulator against the local server: sign in, pay a Sharet member, pay an M-PESA number, and check the balance
    moves by exactly the totals. Typing through the simulator was unreliable, so the test types for us.
13. **Live testing stays on public endpoints.** I did not sign in to sharet.africa with any account; after deploy I
    only check `/api/mobile/v1/config`. Your first sign-in on the live app is yours to do.
14. **Deploy + push:** the mobile API is committed on `v2`, pushed, and deployed with `scripts/deploy.sh`
    (tests, backup, migrate). The release APK points at https://sharet.africa.
