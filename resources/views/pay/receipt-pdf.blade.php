{{-- A transaction receipt as a PDF (App\Support\Receipt::pdf), styled like the Sharet emails (emails/layout): black
     header with the logo and a lime label, black hero with the amount, white body with the lines (no background), black footer.
     The app's fonts (Inter Tight text, Outfit numbers; resources/fonts, TTF, which dompdf reads), a QR code
     with the receipt summary, no borders. dompdf: tables, plain CSS, files from disk. $order = present(),
     $lines = receipt(), $qr = PNG data URI (App\Support\Receipt). --}}
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Sharet receipt {{ $lines['Transaction ID'] ?? '' }}</title>
<style>
  @font-face { font-family: 'Inter Tight'; font-weight: 400; src: url('{{ resource_path('fonts/inter-tight-latin-400-normal.ttf') }}') format('truetype'); }
  @font-face { font-family: 'Inter Tight'; font-weight: 600; src: url('{{ resource_path('fonts/inter-tight-latin-600-normal.ttf') }}') format('truetype'); }
  @font-face { font-family: 'Inter Tight'; font-weight: 700; src: url('{{ resource_path('fonts/inter-tight-latin-700-normal.ttf') }}') format('truetype'); }
  @font-face { font-family: 'Outfit'; font-weight: 400; src: url('{{ resource_path('fonts/outfit-latin-400-normal.ttf') }}') format('truetype'); }
  @font-face { font-family: 'Outfit'; font-weight: 600; src: url('{{ resource_path('fonts/outfit-latin-600-normal.ttf') }}') format('truetype'); }
  @font-face { font-family: 'Outfit'; font-weight: 700; src: url('{{ resource_path('fonts/outfit-latin-700-normal.ttf') }}') format('truetype'); }
  @page { margin: 0; }
  body { margin: 0; background: #FFFFFF; font-family: 'Inter Tight', 'DejaVu Sans', sans-serif; color: #111114; }
  .num { font-family: 'Outfit', 'DejaVu Sans', sans-serif; }
  .wrap { padding: 24px; }
  .card { background: #FFFFFF; border-radius: 12px; overflow: hidden; }
  .dark { background: #0A0A0C; color: #F4F4F0; }
  .eyebrow { font-size: 8px; letter-spacing: 2px; text-transform: uppercase; color: #D4FF3D; text-align: right; }
  .amount { font-family: 'Outfit', 'DejaVu Sans', sans-serif; font-size: 28px; font-weight: 700; margin: 0 0 6px; }
  .lime { color: #D4FF3D; }
  .sub { font-size: 11px; color: #C9C9C2; margin: 0; }
  .box { }
  .box-title { font-size: 8px; letter-spacing: 2px; color: #5A5A55; padding: 4px 0 4px; }
  td.k { font-size: 10px; color: #5A5A55; padding: 6px 0; width: 40%; vertical-align: top; }
  td.v { font-size: 10px; color: #111114; padding: 6px 0; text-align: right; font-weight: 600; }
  .qr { text-align: center; padding: 16px 0 4px; }
  .qr p { font-size: 9px; color: #5A5A55; margin: 6px 0 0; }
  .foot { font-size: 8px; color: #80807A; line-height: 1.6; }
  .foot a { color: #C9C9C2; text-decoration: none; }
</style>
</head>
<body>
<div class="wrap">
  <table class="card" width="100%" cellpadding="0" cellspacing="0">
    <tr><td class="dark" style="padding: 20px 24px 8px;">
      <table width="100%" cellpadding="0" cellspacing="0"><tr>
        <td><img src="{{ public_path('images/email/sharet-logo-email.png') }}" width="96" height="21" alt="Sharet"></td>
        <td class="eyebrow">Transaction receipt</td>
      </tr></table>
    </td></tr>
    <tr><td class="dark" style="padding: 14px 24px 26px;">
      <p class="amount">{{ $order['in'] ? '+' : '-' }} KES <span class="{{ $order['in'] ? 'lime' : '' }}">{{ $order['in'] ? $order['amount'] : $order['total'] }}</span></p>
      <p class="sub">{{ $order['title'] }} · {{ $order['state']['label'] }}</p>
    </td></tr>
    <tr><td style="padding: 22px 24px 10px;">
      <table class="box" width="100%" cellpadding="0" cellspacing="0">
        <tr><td class="box-title">RECEIPT</td></tr>
        <tr><td style="padding: 0 0 10px;">
          <table width="100%" cellpadding="0" cellspacing="0">
            @foreach ($lines as $label => $value)
              <tr><td class="k">{{ $label }}</td><td class="v {{ in_array($label, ['Transaction ID', 'Sharet reference', 'Date', 'Amount', 'Fee and duty', 'Total', 'Account ID'], true) ? 'num' : '' }}">{{ $value }}</td></tr>
            @endforeach
          </table>
        </td></tr>
      </table>
      <div class="qr"><img src="{{ $qr }}" width="110" height="110" alt="Receipt QR code"><p>Scan to read this receipt</p></div>
      <p style="font-size: 10px; color: #5A5A55; margin: 10px 0 6px; text-align: center;">
        @if ($order['sandbox'])Test mode: simulated payment, no real money moved. @endif
        Quote the transaction ID if you contact us about this payment.
      </p>
    </td></tr>
    <tr><td class="dark foot" style="padding: 16px 24px;">
      <a href="{{ url('/') }}">sharet.africa</a> · <a href="mailto:support@sharet.africa">support@sharet.africa</a><br>
      Sharet Africa · Nairobi, Kenya · Cheaper payments across East Africa
    </td></tr>
  </table>
</div>
</body>
</html>
