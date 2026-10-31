{{-- Website analytics report. Data: AnalyticsController@index (own database, no third party). --}}
@extends('layouts.app')
@section('title', 'Website analytics')

@section('content')
@php
    $card = 'rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]';
    $max = max(1, $series->max('views'));
    $n = $series->count();
    $kpis = [
        ['Visitors', number_format($kpi['visitors']), 'Counted per day, no cookies', 'users', 'bg-violet-100 text-violet-600 dark:bg-violet-500/15 dark:text-violet-300'],
        ['Page views', number_format($kpi['views']), $kpi['per_visitor'].' per visitor', 'eye', 'bg-sky-100 text-sky-600 dark:bg-sky-500/15 dark:text-sky-300'],
        ['Time on page', $kpi['avg_time'], 'Average, 1s–30m', 'clock', 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300'],
        ['Bounce rate', $kpi['bounce'].'%', 'Left after one page', 'arrow-uturn-left', 'bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300'],
        ['Scroll depth', $kpi['scroll'].'%', 'How far down people read', 'arrows-up-down', 'bg-rose-100 text-rose-600 dark:bg-rose-500/15 dark:text-rose-300'],
    ];
    $tables = [
        ['Where visitors come from', $referrers, 'Direct visits and search apps that hide the referrer are not listed.'],
        ['Campaigns (utm_campaign)', $campaigns, 'Add ?utm_source=…&utm_campaign=… to links you share to see them here.'],
        ['Devices', $devices, 'No visits yet.'],
        ['Browsers', $browsers, 'No visits yet.'],
        ['Countries', $countries, 'Shown when the site runs behind Cloudflare (CF-IPCountry).'],
    ];
@endphp

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
  <div>
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Website analytics</h1>
    <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">What sharet.africa visitors read and click. Stored in our own database: no cookies, no IP addresses, no third parties. Visitors who ask not to be tracked are skipped.</p>
  </div>
  <div class="flex flex-wrap gap-2">
    <a href="{{ route('admin.analytics.heatmap', ['days' => $days]) }}" class="inline-flex h-10 items-center gap-2 rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
      <x-heroicon-o-fire class="h-5 w-5 text-orange-500" /> Click heatmap
    </a>
    <div class="flex rounded-lg border border-gray-200 bg-white p-1 dark:border-gray-800 dark:bg-white/[0.03]">
      @foreach ($ranges as $value => $label)
        <a href="?days={{ $value }}" @class(['rounded-md px-3 py-1.5 text-sm font-medium', 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' => $days === $value, 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' => $days !== $value])>{{ str_replace('Last ', '', $label) }}</a>
      @endforeach
    </div>
  </div>
</div>

<div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
  @foreach ($kpis as [$label, $value, $hint, $icon, $tone])
    <div class="{{ $card }}">
      <div class="flex items-center gap-3">
        <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $tone }}"><x-dynamic-component :component="'heroicon-o-'.$icon" class="h-5 w-5" /></span>
        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</span>
      </div>
      <p class="mt-4 text-2xl font-semibold text-gray-900 dark:text-white">{{ $value }}</p>
      <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</p>
    </div>
  @endforeach
</div>

<div class="{{ $card }} mb-6">
  <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
    <h2 class="font-semibold text-gray-900 dark:text-white">Views per day</h2>
    <div class="flex gap-4 text-xs text-gray-500 dark:text-gray-400">
      <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-violet-500"></span>Views</span>
      <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-[#9CCB1F]"></span>Visitors</span>
    </div>
  </div>
  @if ($kpi['views'] === 0)
    <p class="py-16 text-center text-sm text-gray-500 dark:text-gray-400">No visits recorded in this period yet. Tracking starts once this version is live.</p>
  @else
    <svg viewBox="0 0 {{ $n * 10 }} 120" preserveAspectRatio="none" class="h-56 w-full" role="img" aria-label="Views per day chart">
      @foreach ($series as $i => $day)
        @php $h = $day['views'] / $max * 110; $hv = $day['visitors'] / $max * 110; @endphp
        <rect x="{{ $i * 10 + 1 }}" y="{{ 115 - $h }}" width="8" height="{{ max($h, 0.5) }}" rx="1" class="fill-violet-500/80"><title>{{ \Carbon\Carbon::parse($day['date'])->format('D j M') }}: {{ $day['views'] }} views, {{ $day['visitors'] }} visitors</title></rect>
        <rect x="{{ $i * 10 + 3 }}" y="{{ 115 - $hv }}" width="4" height="{{ max($hv, 0.5) }}" rx="1" fill="#9CCB1F" />
      @endforeach
    </svg>
    <div class="mt-2 flex justify-between text-xs text-gray-400">
      <span>{{ \Carbon\Carbon::parse($series->first()['date'])->format('j M') }}</span>
      <span>{{ \Carbon\Carbon::parse($series->last()['date'])->format('j M') }}</span>
    </div>
  @endif
</div>

<div class="mb-6 grid gap-6 xl:grid-cols-3">
  <div class="{{ $card }} overflow-x-auto xl:col-span-2">
    <h2 class="mb-3 font-semibold text-gray-900 dark:text-white">Top pages</h2>
    <table class="min-w-full text-left text-sm">
      <thead><tr class="border-b border-gray-100 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
        <th class="py-2 pr-4 font-medium">Page</th><th class="py-2 pr-4 font-medium">Views</th><th class="py-2 pr-4 font-medium">Visitors</th><th class="py-2 pr-4 font-medium">Time</th><th class="py-2 pr-4 font-medium">Scroll</th><th></th>
      </tr></thead>
      <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
        @forelse ($pages as $p)
          <tr class="text-gray-700 dark:text-gray-300">
            <td class="max-w-[260px] truncate py-2.5 pr-4 font-medium"><a href="{{ url($p->path) }}" target="_blank" rel="noopener" class="hover:underline">{{ $p->path }}</a></td>
            <td class="py-2.5 pr-4">{{ number_format($p->views) }}</td>
            <td class="py-2.5 pr-4">{{ number_format($p->visitors) }}</td>
            <td class="py-2.5 pr-4">{{ $duration($p->avg_ms) }}</td>
            <td class="py-2.5 pr-4">{{ $p->scroll ? round($p->scroll).'%' : '—' }}</td>
            <td class="py-2.5"><a href="{{ route('admin.analytics.heatmap', ['path' => $p->path, 'days' => $days]) }}" class="text-xs font-medium text-orange-600 hover:underline dark:text-orange-400">Heatmap</a></td>
          </tr>
        @empty
          <tr><td colspan="6" class="py-8 text-center text-gray-500 dark:text-gray-400">No page views yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="{{ $card }}">
    <h2 class="mb-3 font-semibold text-gray-900 dark:text-white">Most clicked</h2>
    <ul class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
      @forelse ($clicks as $c)
        <li class="flex items-center justify-between gap-3 py-2.5">
          <span class="min-w-0"><span class="block truncate font-medium text-gray-800 dark:text-gray-200">{{ $c->target }}</span><span class="text-xs text-gray-500">{{ $c->path }}</span></span>
          <span class="shrink-0 text-gray-500 dark:text-gray-400">{{ number_format($c->clicks) }}</span>
        </li>
      @empty
        <li class="py-8 text-center text-gray-500 dark:text-gray-400">No clicks yet.</li>
      @endforelse
    </ul>
  </div>
</div>

<div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
  @foreach ($tables as [$title, $rows, $empty])
    @php $sum = max(1, $rows->sum('views')); @endphp
    <div class="{{ $card }}">
      <h2 class="mb-3 font-semibold text-gray-900 dark:text-white">{{ $title }}</h2>
      @forelse ($rows as $r)
        <div class="mb-3">
          <div class="mb-1 flex justify-between text-sm"><span class="truncate text-gray-700 dark:text-gray-300">{{ ucfirst($r->label) }}</span><span class="text-gray-500">{{ number_format($r->views) }}</span></div>
          <div class="h-1.5 rounded-full bg-gray-100 dark:bg-white/5"><div class="h-1.5 rounded-full bg-violet-500" style="width: {{ round($r->views / $sum * 100) }}%"></div></div>
        </div>
      @empty
        <p class="py-4 text-sm text-gray-500 dark:text-gray-400">{{ $empty }}</p>
      @endforelse
    </div>
  @endforeach
</div>
@endsection
