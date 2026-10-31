{{-- Sharet email shell. Table layout + inline styles for Gmail/Outlook; dark mode where the client supports it.
     Sections: preheader (inbox preview), content. Logos are PNG because most mail apps block SVG. --}}
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="color-scheme" content="light dark">
  <meta name="supported-color-schemes" content="light dark">
  <title>{{ $subjectLine ?? 'Sharet' }}</title>
  <style>
    body { margin:0; padding:0; background:#F4F4F0; }
    a { color:#0A0A0C; }
    .logo-dark { display:none; }
    @media (max-width: 600px) { .card { padding:28px 22px !important; } .h1 { font-size:24px !important; } }
    @media (prefers-color-scheme: dark) {
      body, .bg { background:#0A0A0C !important; }
      .card { background:#18181C !important; border-color:#2A2A30 !important; }
      .text, .h1, .cell { color:#F4F4F0 !important; }
      .muted { color:#A3A39C !important; }
      .rule { border-color:#2A2A30 !important; }
      .logo-light { display:none !important; }
      .logo-dark { display:block !important; }
      a { color:#D4FF3D !important; }
      .btn a { color:#0A0A0C !important; }
    }
  </style>
</head>
<body class="bg" style="margin:0; padding:0; background:#F4F4F0;">
  <div style="display:none; max-height:0; overflow:hidden; opacity:0;">{{ $preheader ?? '' }}&#8203;&nbsp;&#8203;&nbsp;&#8203;&nbsp;</div>

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="bg" style="background:#F4F4F0;">
    <tr><td align="center" style="padding:32px 12px;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;">
        <tr><td style="padding:0 4px 20px;">
          <a href="{{ url('/') }}" style="text-decoration:none;">
            <img class="logo-light" src="{{ asset('images/email/sharet-logo.png') }}" width="161" height="40" alt="Sharet Africa" style="display:block; border:0;">
            <img class="logo-dark" src="{{ asset('images/email/sharet-logo-dark.png') }}" width="161" height="40" alt="Sharet Africa" style="border:0;">
          </a>
        </td></tr>

        <tr><td class="card" style="background:#FFFFFF; border:1px solid #E5E5DE; border-radius:20px; padding:36px 36px; font-family:Outfit, 'Segoe UI', Helvetica, Arial, sans-serif;">
          @yield('content')
        </td></tr>

        <tr><td style="padding:24px 8px 0; font-family:Outfit, 'Segoe UI', Helvetica, Arial, sans-serif; font-size:12px; line-height:18px;" class="muted">
          <p class="muted" style="margin:0 0 6px; color:#80807A;">Sharet Africa · Nairobi, Kenya · Cheaper payments across East Africa</p>
          <p class="muted" style="margin:0; color:#80807A;">
            <a href="{{ url('/') }}" style="color:#80807A;">sharet.africa</a> ·
            <a href="mailto:support@sharet.africa" style="color:#80807A;">support@sharet.africa</a>
            @isset($footerNote) · {{ $footerNote }} @endisset
          </p>
        </td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
