{{-- Administration › Growth shell: header, tabs, site + period filters. Tabs extend this. Data: Admin\GrowthController. --}}
@extends('layouts.app')
@section('title', 'Growth · '.$tabs[$tab === 'overview' ? 'index' : $tab])

@section('content')
@php
    $q = fn (array $over = []) => array_filter(array_merge(['site' => $site, 'days' => $days === 30 ? null : $days], $over), fn ($v) => $v !== null && $v !== '');
    $routeFor = fn ($t) => 'admin.growth.'.$t;
    $current = $tab === 'overview' ? 'index' : $tab;
@endphp
<div>
  <x-ui.page-header title="Growth" subtitle="All seven Sharet-family sites in one place: visits, conversions, SEO findings and what to push next. First-party data from sharet-analytics.js (no cookies, no IP addresses, Do-Not-Track respected) and a daily SEO audit." />

  @if (session('status'))
    <div class="mb-4 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">{{ session('status') }}</div>
  @endif

  <div class="mb-4 flex gap-1 overflow-x-auto rounded-xl border border-gray-200 bg-white p-1 dark:border-gray-800 dark:bg-white/[0.03]" role="tablist">
    @foreach ($tabs as $key => $label)
      <a href="{{ route($routeFor($key), $q()) }}" role="tab" aria-selected="{{ $current === $key ? 'true' : 'false' }}"
         @class(['whitespace-nowrap rounded-lg px-4 py-2 text-sm font-medium', 'bg-primary text-gray-900' => $current === $key, 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5' => $current !== $key])>{{ $label }}</a>
    @endforeach
  </div>

  <div class="mb-6 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
    <div class="flex flex-wrap gap-1" aria-label="Site">
      <a href="{{ route($routeFor($current), $q(['site' => null])) }}" @class(['rounded-full border px-3 py-1 text-xs font-medium', 'border-gray-900 bg-gray-900 text-white dark:border-white dark:bg-white dark:text-gray-900' => ! $site, 'border-gray-300 text-gray-600 hover:border-gray-500 dark:border-gray-700 dark:text-gray-300' => $site])>All sites</a>
      @foreach ($sites as $key => $s)
        <a href="{{ route($routeFor($current), $q(['site' => $key])) }}" @class(['inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-medium', 'border-gray-900 bg-gray-900 text-white dark:border-white dark:bg-white dark:text-gray-900' => $site === $key, 'border-gray-300 text-gray-600 hover:border-gray-500 dark:border-gray-700 dark:text-gray-300' => $site !== $key])><span class="h-2 w-2 rounded-full" style="background: {{ $s['color'] }}"></span>{{ $s['name'] }}</a>
      @endforeach
    </div>
    <div class="flex w-fit rounded-lg border border-gray-200 bg-white p-1 dark:border-gray-800 dark:bg-white/[0.03]" aria-label="Period">
      @foreach ($ranges as $value => $label)
        <a href="{{ route($routeFor($current), $q(['days' => $value === 30 ? null : $value])) }}" @class(['whitespace-nowrap rounded-md px-3 py-1.5 text-sm font-medium', 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' => $days === $value, 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' => $days !== $value])>{{ $label }}</a>
      @endforeach
    </div>
  </div>

  @if (! $ready)
    <x-ui.card>
      <p class="text-sm font-medium text-gray-900 dark:text-white">The Growth tables are not set up yet.</p>
      <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">Apply <code>database/schema/2026_11_10_growth_hub.sql</code> (e.g. <code>mysql shar_main &lt; database/schema/2026_11_10_growth_hub.sql</code>). The collector starts recording within ten minutes.</p>
    </x-ui.card>
  @else
    @yield('growth')
  @endif
</div>
@endsection
