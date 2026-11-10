{{-- Signed-out pages (sign in, create account, password reset, email code) in the Sharet UI (canvas A01/A02):
     the Sharet logo, then one calm white card with a big friendly headline and pill buttons. Phones: the card is the
     page (white, edge to edge); wider screens: a white card on paper. Login and register add the A01 welcome panel
     beside the card on desktop (@section('welcome')). Same theme channels as the app (body.sharet-app), light first. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex,follow">
    <title>@yield('title', 'Sharet') · Sharet Africa</title>
    @hasSection('description')<meta name="description" content="@yield('description')">@endif

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#ffffff">
    <style>[x-cloak]{display:none!important}</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="sharet-app min-h-screen bg-app-panel text-app-ink antialiased md:bg-app-bg">
<div class="mx-auto flex min-h-screen w-full max-w-[1040px] flex-col px-4 md:px-6">
  <header class="flex h-16 items-center justify-between md:h-20">
    <a href="{{ route('home') }}" aria-label="Sharet home"><img src="{{ asset('images/brand/sharet-logo-colour-on-light.svg') }}" alt="Sharet" class="h-6 w-auto md:h-7"></a>
    @yield('switch')
  </header>

  <main id="main" class="flex flex-1 items-start justify-center pb-10 md:items-center md:py-8">
    <div @class(['grid w-full items-stretch gap-6', 'lg:grid-cols-2' => View::hasSection('welcome'), 'max-w-[440px]' => ! View::hasSection('welcome')])>
      @hasSection('welcome')
        <aside class="hidden lg:block">@yield('welcome')</aside>
      @endif
      <div class="w-full md:mx-auto md:max-w-[440px] md:rounded-[28px] md:bg-app-panel md:p-8 md:shadow-[0_1px_2px_rgb(10_10_12/0.04),0_10px_30px_-18px_rgb(10_10_12/0.18)]">
        @hasSection('icon')
          <span class="mb-5 flex h-14 w-14 items-center justify-center rounded-full bg-app-lime text-[#050507]">@yield('icon')</span>
        @endif
        <h1 class="app-display !text-[28px] !leading-tight">@yield('heading')</h1>
        @hasSection('lede')<p class="mt-2 text-[15px] leading-relaxed text-app-muted">@yield('lede')</p>@endif

        @if (session('status') && ! View::hasSection('own-status'))
          <p class="mt-5 flex gap-2 rounded-2xl bg-app-in/10 px-4 py-3 text-sm text-app-in" role="status">
            <x-heroicon-o-check-circle class="h-5 w-5 shrink-0" /> <span>{{ session('status') }}</span>
          </p>
        @endif

        <div class="mt-6">@yield('content')</div>
      </div>
    </div>
  </main>

  <footer class="flex flex-wrap items-center justify-center gap-x-4 gap-y-1 pb-6 text-xs text-app-muted">
    <span>Sharet Africa</span>
    <a href="{{ route('privacy') }}" class="hover:text-app-ink">Privacy</a>
    <a href="{{ route('contact') }}" class="hover:text-app-ink">Contact</a>
    <a href="mailto:support@sharet.africa" class="hover:text-app-ink">support@sharet.africa</a>
  </footer>
</div>
@if (session()->has('sa_conversion'))
@include('partials.sharet-analytics')
@endif
@stack('scripts')
</body>
</html>
