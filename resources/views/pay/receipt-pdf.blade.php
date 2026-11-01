{{-- A transaction receipt as a PDF (App\Support\Receipt::pdf), in the Sharet email colours: a black header edge to edge
     (logo and amount on the left, the label and QR code on the right), the lines centred on white, a black footer at
     the bottom of the page. Square corners, no background boxes.
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
  .dark { background: #0A0A0C; color: #F4F4F0; }
  .eyebrow { font-size: 8px; letter-spacing: 2px; text-transform: uppercase; color: #D4FF3D; }
  .amount { font-family: 'Outfit', 'DejaVu Sans', sans-serif; font-size: 30px; font-weight: 700; margin: 18px 0 6px; }
  .lime { color: #D4FF3D; }
  .sub { font-size: 11px; color: #C9C9C2; margin: 0; }
  .qr { background: #FFFFFF; padding: 4px; }
  .box-title { font-size: 8px; letter-spacing: 2px; color: #5A5A55; text-align: center; padding: 0 0 8px; }
  td.k { font-size: 10px; color: #5A5A55; padding: 6px 0; width: 45%; vertical-align: top; }
  td.v { font-size: 10px; color: #111114; padding: 6px 0; text-align: right; font-weight: 600; }
  .foot { position: fixed; left: 0; right: 0; bottom: 0; padding: 18px 40px; font-size: 8px; color: #80807A; line-height: 1.6; }
  .foot a { color: #C9C9C2; text-decoration: none; }
</style>
</head>
<body>
  {{-- Header: black, edge to edge, square corners; the amount on the left, the QR under the label on the right --}}
  <table class="dark" width="100%" cellpadding="0" cellspacing="0">
    <tr>
      <td style="padding: 32px 40px 32px; vertical-align: top;">
        <img src="{{ public_path('images/email/sharet-logo-email.png') }}" width="104" height="23" alt="Sharet">
        <p class="amount">{{ $order['in'] ? '+' : '-' }} KES <span class="{{ $order['in'] ? 'lime' : '' }}">{{ $order['in'] ? $order['amount'] : $order['total'] }}</span></p>
        <p class="sub">{{ $order['title'] }} · {{ $order['state']['label'] }}</p>
      </td>
      <td style="padding: 32px 40px 32px 0; vertical-align: top; text-align: right; width: 130px;">
        <p class="eyebrow" style="margin: 4px 0 12px;">Transaction receipt</p>
        <img class="qr" src="{{ $qr }}" width="96" height="96" alt="Receipt QR code">
      </td>
    </tr>
  </table>

  {{-- The receipt lines, centred on the page --}}
  <table width="64%" cellpadding="0" cellspacing="0" style="margin: 36px auto 0;">
    <tr><td colspan="2" class="box-title">RECEIPT</td></tr>
    @foreach ($lines as $label => $value)
      <tr><td class="k">{{ $label }}</td><td class="v {{ in_array($label, ['Transaction ID', 'Sharet reference', 'Date', 'Amount', 'Fee and duty', 'Total', 'Account ID'], true) ? 'num' : '' }}">{{ $value }}</td></tr>
    @endforeach
  </table>
  <p style="font-size: 9px; color: #5A5A55; margin: 24px auto 0; width: 64%; text-align: center; line-height: 1.6;">
    @if ($order['sandbox'])Test mode: simulated payment, no real money moved.<br>@endif
    Quote the transaction ID if you contact us about this payment. The QR code holds this receipt's summary.
  </p>

  {{-- Footer: black, edge to edge, square corners, at the bottom of the page --}}
  <div class="dark foot">
    <a href="{{ url('/') }}">sharet.africa</a> · <a href="mailto:support@sharet.africa">support@sharet.africa</a><br>
    Sharet Africa · Nairobi, Kenya · Cheaper payments across East Africa
  </div>
</body>
</html>
