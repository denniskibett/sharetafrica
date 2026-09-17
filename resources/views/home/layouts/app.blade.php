{{-- resources/views/home/layouts/app.blade.php --}}
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>@yield('title', 'Sharet Africa')</title>
  <meta name="description" content="@yield('description', 'Cheaper payments across East Africa. Any rail. The other person doesn\'t need Sharet.')" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Boldonse&family=Inter+Tight:wght@400;500;600;700&family=Geist+Mono:wght@400;500&display=swap" />
  @vite(['resources/css/styles.css', 'resources/js/site.js'])
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