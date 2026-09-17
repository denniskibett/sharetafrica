{{-- resources/views/home/layouts/header.blade.php --}}
<header class="site-header">
  <div class="container container--wide">
    <nav class="nav" aria-label="Primary">
      <a class="brand" href="{{ route('home') }}">
        <span class="brand-mark" aria-hidden="true"></span> Sharet!
      </a>
      <div class="nav-links" role="navigation">
        <a href="{{ route('merchants') }}" @if(request()->routeIs('merchants')) aria-current="page" @endif>Merchants</a>
        <a href="{{ route('personal') }}" @if(request()->routeIs('personal')) aria-current="page" @endif>Personal</a>
        <a href="{{ route('trade') }}" @if(request()->routeIs('trade')) aria-current="page" @endif>Trade</a>
        <a href="{{ route('developers') }}" @if(request()->routeIs('developers')) aria-current="page" @endif>Developers</a>
        <a href="{{ route('company') }}" @if(request()->routeIs('company')) aria-current="page" @endif>Company</a>
        <a href="{{ route('contact') }}" @if(request()->routeIs('contact')) aria-current="page" @endif>Contact</a>
      </div>
      <div class="nav-cta-row">
        <a href="{{ route('login') }}" class="btn btn--primary btn--sm">Get started
          <svg class="arrow" width="14" height="10" viewBox="0 0 14 10" fill="none" aria-hidden="true"><path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </a>
        <button class="nav-toggle" aria-label="Open menu" aria-expanded="false" aria-controls="mobile-drawer"><span aria-hidden="true"></span></button>
      </div>
    </nav>
  </div>
</header>

<div class="mobile-drawer" id="mobile-drawer" aria-hidden="true">
  <button class="drawer-close" aria-label="Close menu">Close</button>
  <a href="{{ route('home') }}">Home</a>
  <a href="{{ route('merchants') }}">Merchants</a>
  <a href="{{ route('personal') }}">Personal</a>
  <a href="{{ route('trade') }}">Trade</a>
  <a href="{{ route('developers') }}">Developers</a>
  <a href="{{ route('company') }}">Company</a>
  <a href="{{ route('contact') }}">Contact</a>
</div>