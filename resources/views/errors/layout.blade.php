{{-- Error pages: self-contained (no Vite, no database) so they still render when the app is broken.
     Pass $code, $title, $message; optional $tone (hex accent) and $retry (show "Try again"). --}}
@php
    $ref = \App\Support\ErrorRef::get();
    $tone = $tone ?? '#D4FF3D';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title>{{ $code }} · {{ $title }} · Sharet</title>
  <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
  <style>
    :root { --bg:#F4F4F0; --card:#FFFFFF; --ink:#0A0A0C; --soft:#5A5A55; --line:rgba(10,10,12,.1); --tone:{{ $tone }}; color-scheme: light dark; }
    @media (prefers-color-scheme: dark) { :root { --bg:#0A0A0C; --card:#18181C; --ink:#F4F4F0; --soft:#C9C9C2; --line:rgba(244,244,240,.12); } }
    * { box-sizing: border-box; }
    body { margin:0; min-height:100vh; display:grid; place-items:center; padding:24px 16px; background:var(--bg); color:var(--ink);
           font:16px/1.5 Outfit, ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif; }
    main { width:100%; max-width:520px; background:var(--card); border:1px solid var(--line); border-radius:24px; padding:32px 28px; }
    .brand { display:inline-flex; align-items:center; gap:10px; font-weight:800; letter-spacing:-.01em; text-transform:uppercase; color:var(--ink); text-decoration:none; }
    .code { margin:28px 0 4px; font-size:72px; line-height:1; font-weight:800; letter-spacing:-.04em; }
    .code span { background:linear-gradient(transparent 62%, var(--tone) 62%); padding:0 4px; color:var(--ink); }
    h1 { margin:8px 0 8px; font-size:22px; }
    p { margin:0; color:var(--soft); }
    .actions { display:flex; flex-wrap:wrap; gap:10px; margin-top:24px; }
    .btn { display:inline-flex; align-items:center; min-height:44px; padding:0 18px; border-radius:12px; font-weight:600; font-size:15px; text-decoration:none; border:1px solid var(--line); color:var(--ink); background:transparent; cursor:pointer; font-family:inherit; }
    .btn.primary { background:#D4FF3D; color:#0A0A0C; border-color:#D4FF3D; }
    .ref { margin-top:28px; padding-top:16px; border-top:1px solid var(--line); font-size:13px; color:var(--soft); display:flex; flex-wrap:wrap; gap:4px 16px; }
    .ref code { font-family:ui-monospace, SFMono-Regular, Menlo, monospace; color:var(--ink); }
  </style>
</head>
<body>
  <main>
    <a class="brand" href="{{ url('/') }}">
      <picture>
        <source srcset="{{ asset('images/brand/sharet-logo-colour-on-dark.svg') }}" media="(prefers-color-scheme: dark)">
        <img src="{{ asset('images/brand/sharet-logo-colour-on-light.svg') }}" alt="Sharet Africa" height="44" style="display:block;height:44px;width:auto">
      </picture>
    </a>

    <div class="code"><span>{{ $code }}</span></div>
    <h1>{{ $title }}</h1>
    <p>{{ $message }}</p>

    <div class="actions">
      @if (! empty($retry))
        <a class="btn primary" href="{{ $code == 419 ? url()->previous() : url()->current() }}">Try again</a>
        <a class="btn" href="{{ url('/') }}">Go to sharet.africa</a>
      @else
        <a class="btn primary" href="{{ url('/') }}">Go to sharet.africa</a>
        <button class="btn" type="button" onclick="history.length > 1 ? history.back() : location.assign('{{ url('/') }}')">Go back</button>
      @endif
      <a class="btn" href="mailto:support@sharet.africa?subject={{ rawurlencode("Error $code · Ref $ref") }}">Contact support</a>
    </div>

    <div class="ref">
      <span>Reference <code>{{ $ref }}</code></span>
      <span>{{ now()->timezone('Africa/Nairobi')->format('j M Y, H:i') }} EAT</span>
    </div>
  </main>
</body>
</html>
