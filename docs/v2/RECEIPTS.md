# Receipts

One definition, three places: the receipt panel, a PDF to download, and an email.

- **The lines** come from `RampOrder::receipt($viewer)`: recipient, account ID, bank (or M-PESA / Sharet wallet),
  from, transaction ID (the M-PESA receipt of the money that arrived, else Sharet's own `SH…` reference), Sharet
  reference, date, amount, fee and duty, total, status, note. A person paid on an @name transfer never sees the sender's
  number.
- **The panel** (`resources/views/pay/partials/detail.blade.php`) lists them with Copy, Download and Share. Share
  uses the phone's share sheet with a short M-PESA-style text, or copies it.
- **The PDF** (`GET /pay/{order}/receipt`, `App\Support\Receipt::pdf`, view `pay/receipt-pdf`): A5, black header edge to
  edge with the logo, the amount (lime) and a QR code under "TRANSACTION RECEIPT", the lines centred on white, a black
  footer at the bottom. Fonts are the app's (Outfit, Inter Tight) as TTF in `resources/fonts` (dompdf cannot read WOFF2);
  the font cache is `storage/fonts`.
- **The QR code** holds a plain-text summary (`Receipt::qrText`): transaction ID, amount, recipient and account, date,
  status. Any phone camera reads it, even offline.
- **The email** (`SharetMail::transactionReceipt`, the standard Sharet email design, PDF attached) goes out when a
  payment finishes (paid or refunded) and its amount is at least `RECEIPT_EMAIL_MIN` (default 100000 cents = KES
  1,000.00, `config/pricing.php`). The payer gets it; on an @name transfer the person paid gets their own. It is sent
  after the database commits, and a failed email is reported but never touches the payment.

Tests: `tests/Feature/Ramp/WalletPaymentsTest.php` (lines, PDF download, owner-only access, threshold, both people on
a transfer).

## SMS (KenyaSMS)

`App\Support\CustomerMessages` texts the payer as a payment moves: when the M-PESA prompt goes out ("approve KES X on
your phone"), and when it ends (paid, added to wallet, not paid, refunded), with the reference. On an @name transfer the
person paid gets their own message. Sent through `App\Integrations\Sms\KenyaSms` (`POST {api_url}/sms/send`, Bearer
key, sender ID from the KenyaSMS integration), once per payment and status (KenyaSMS `idempotency_key`), after the
database commits. Not set up, or KenyaSMS down: payments carry on and the failure is reported.
