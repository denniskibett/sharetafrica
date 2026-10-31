{{-- Sharet email shell, matching the brand canvas email (docs/brand/project/MktEmail.dc.html) and the landing page:
     grey page, white card, black header with the compact logo and a lime label, black hero, white body, black footer.
     Tables + inline styles for Gmail/Outlook. Sections: hero, content. Logo is PNG because most mail apps block SVG. --}}
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="color-scheme" content="light only">
  <meta name="supported-color-schemes" content="light only">
  <title>{{ $subjectLine ?? 'Sharet' }}</title>
  <link href="https://fonts.googleapis.com/css2?family=Boldonse&family=Inter+Tight:wght@400;500;600;700&family=Geist+Mono:wght@500&display=swap" rel="stylesheet">
  <style>
    body { margin:0; padding:0; background:#E5E5DE; }
    @media (max-width: 600px) { .px { padding-left:22px !important; padding-right:22px !important; } .h1 { font-size:24px !important; } }
  </style>
</head>
<body style="margin:0; padding:0; background:#E5E5DE;">
  <div style="display:none; max-height:0; overflow:hidden; opacity:0;">{{ $preheader ?? '' }}&#8203;&nbsp;&#8203;&nbsp;&#8203;&nbsp;</div>

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#E5E5DE;">
    <tr><td align="center" style="padding:24px 12px;">
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px; background:#FFFFFF; border-radius:12px; overflow:hidden; font-family:'Inter Tight', 'Segoe UI', Helvetica, Arial, sans-serif;">
        {{-- Header: logo + label --}}
        <tr><td class="px" style="background:#0A0A0C; padding:28px 36px 12px;">
          <table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>
            <td><a href="{{ url('/') }}" style="text-decoration:none;"><img src="{{ asset('images/email/sharet-logo-email.png') }}" width="120" height="26" alt="Sharet" style="display:block; border:0;"></a></td>
            @isset($eyebrow)<td align="right" style="font-family:'Geist Mono', ui-monospace, Menlo, monospace; font-size:11px; letter-spacing:.2em; text-transform:uppercase; color:#D4FF3D;">{{ $eyebrow }}</td>@endisset
          </tr></table>
        </td></tr>

        {{-- Hero (black) --}}
        <tr><td class="px" style="background:#0A0A0C; color:#F4F4F0; padding:16px 36px 40px;">
          @yield('hero')
        </td></tr>

        {{-- Body (white) --}}
        <tr><td class="px" style="padding:36px 36px 12px; color:#111114;">
          @yield('content')
        </td></tr>

        {{-- Footer (black) --}}
        <tr><td class="px" style="background:#0A0A0C; color:#80807A; padding:28px 36px; font-size:12px; line-height:19px;">
          <p style="margin:0 0 10px;">
            <a href="{{ url('/') }}" style="color:#C9C9C2;">sharet.africa</a>&nbsp;&nbsp;·&nbsp;&nbsp;<a href="{{ url('/blog') }}" style="color:#C9C9C2;">Blog</a>&nbsp;&nbsp;·&nbsp;&nbsp;<a href="mailto:support@sharet.africa" style="color:#C9C9C2;">support@sharet.africa</a>
          </p>
          <p style="margin:0 0 6px;">Sharet Africa · Nairobi, Kenya · Cheaper payments across East Africa</p>
          <p style="margin:0;">{{ $footerNote ?? "You're receiving this because you contacted Sharet or have a Sharet account." }}</p>
        </td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
