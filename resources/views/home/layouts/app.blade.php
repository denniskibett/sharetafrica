{{-- resources/views/home/layouts/app.blade.php --}}
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  @php
      $seoTitle = trim($__env->yieldContent('title', 'Sharet Africa'));
      $seoDescription = trim($__env->yieldContent('description', "Cheaper payments across East Africa. Any rail. The other person doesn't need Sharet."));
      $seoImage = asset('images/og/sharet-og.png');
  @endphp
  <title>{{ $seoTitle }}</title>
  <meta name="description" content="{{ $seoDescription }}" />
  <meta name="robots" content="@yield('robots', 'index,follow,max-image-preview:large,max-snippet:-1')" />
  <link rel="canonical" href="{{ url()->current() }}" />
  <link rel="alternate" hreflang="en-KE" href="{{ url()->current() }}" />
  <link rel="alternate" hreflang="x-default" href="{{ url()->current() }}" />
  <link rel="alternate" type="text/plain" title="Sharet summary for AI assistants" href="{{ route('llms') }}" />
  <link rel="sitemap" type="application/xml" href="{{ route('sitemap') }}" />

  {{-- Share cards (WhatsApp, LinkedIn, X, Facebook…) --}}
  <meta property="og:type" content="website" />
  <meta property="og:site_name" content="Sharet Africa" />
  <meta property="og:locale" content="en_KE" />
  <meta property="og:url" content="{{ url()->current() }}" />
  <meta property="og:title" content="{{ $seoTitle }}" />
  <meta property="og:description" content="{{ $seoDescription }}" />
  <meta property="og:image" content="{{ $seoImage }}" />
  <meta property="og:image:width" content="1200" />
  <meta property="og:image:height" content="630" />
  <meta property="og:image:alt" content="Sharet: cheaper payments across East Africa" />
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="{{ $seoTitle }}" />
  <meta name="twitter:description" content="{{ $seoDescription }}" />
  <meta name="twitter:image" content="{{ $seoImage }}" />

  {{-- Local search: Nairobi, Kenya --}}
  <meta name="geo.region" content="KE-30" />
  <meta name="geo.placename" content="Nairobi" />
  <meta name="geo.position" content="-1.286389;36.817223" />
  <meta name="ICBM" content="-1.286389, 36.817223" />

  <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}" />
  <link rel="alternate icon" href="{{ asset('favicon.ico') }}" />
  <link rel="apple-touch-icon" href="{{ asset('images/logo/logo-icon.svg') }}" />
  <meta name="theme-color" content="#0A0A0C" />

  <script type="application/ld+json">@json(\App\Support\Seo::graph($seoTitle, $seoDescription), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)</script>

  <link rel="preconnect" href="https://images.unsplash.com" crossorigin />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Boldonse&family=Inter+Tight:wght@400;500;600;700&family=Geist+Mono:wght@400;500&display=swap" />
  @vite(['resources/css/styles.css', 'resources/js/site.js'])
  {{-- Instant page changes: the browser fetches a link's page when you hover it (Chrome/Edge; others ignore) --}}
  <script type="speculationrules">{"prefetch":[{"where":{"and":[{"href_matches":"/*"},{"not":{"href_matches":["/logout","/t","/*\\?*","/admin/emails/*"]}},{"not":{"selector_matches":"[data-no-prefetch], [target=_blank]"}}]},"eagerness":"moderate"}]}</script>

  @stack('head')
</head>
<body>
  <a class="skip-link" href="#main">Skip to content</a>

  @include('home.layouts.header')

  <main id="main">
    @include('partials.alert.flash')
    @yield('content')
  </main>

  @include('home.layouts.footer')

  @stack('scripts')
</body>
</html>