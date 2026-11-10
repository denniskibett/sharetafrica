{{-- Growth › Conversions: by site × name, by source / medium / campaign, by landing page, and a simple funnel. --}}
@extends('admin.growth.layout')

@section('growth')
@php
    $th = 'px-3 py-2 text-xs font-medium whitespace-nowrap text-gray-500 dark:text-gray-400';
    $td = 'px-3 py-2.5 text-sm';
    $name = fn ($k) => $sites[$k]['name'] ?? $k;
    $dot = fn ($k) => '<span class="mr-1.5 inline-block h-2 w-2 rounded-full" style="background:'.e($sites[$k]['color'] ?? '#999').'"></span>';
    $pct = fn ($r) => $r === null ? '—' : $r.'%';
@endphp

<x-ui.card class="mb-6" title="Funnel" subtitle="Visits → engaged (2+ pages, or 30 s+ on a page) → visits with a conversion. Conversions come from the sites' thank-you pages (window.sa)." :padded="false">
  <div class="overflow-x-auto">
    <table class="w-full text-left text-sm">
      <thead class="border-y border-gray-100 dark:border-gray-800"><tr><th class="{{ $th }}">Site</th><th class="{{ $th }}">Visits</th><th class="{{ $th }}">Engaged</th><th class="{{ $th }}">Converted</th><th class="{{ $th }} w-1/3"></th></tr></thead>
      <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
        @foreach ($funnel as $f)
          @php $base = max(1, $f->visits); @endphp
          <tr>
            <td class="{{ $td }} font-medium text-gray-900 dark:text-white">{!! $dot($f->key) !!}{{ $f->name }}</td>
            <td class="{{ $td }}">{{ number_format($f->visits) }}</td>
            <td class="{{ $td }}">{{ number_format($f->engaged) }} <span class="text-xs text-gray-500">{{ $f->visits ? round($f->engaged / $base * 100) : 0 }}%</span></td>
            <td class="{{ $td }}">{{ number_format($f->converted) }} <span class="text-xs text-gray-500">{{ $f->visits ? round($f->converted / $base * 100, 1) : 0 }}%</span></td>
            <td class="{{ $td }}">
              <div class="h-2 rounded-full bg-gray-100 dark:bg-white/10"><div class="h-2 rounded-full bg-gray-400" style="width: {{ $f->visits ? 100 : 0 }}%">
                <div class="h-2 rounded-full bg-emerald-500" style="width: {{ round($f->engaged / $base * 100) }}%"><div class="h-2 rounded-full bg-red-600" style="width: {{ $f->engaged ? min(100, round($f->converted / max(1, $f->engaged) * 100)) : 0 }}%"></div></div>
              </div></div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</x-ui.card>

<div class="mb-6 grid gap-6 xl:grid-cols-3">
  <x-ui.card title="By site and conversion" subtitle="What people did">
    <ul class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
      @forelse ($bySiteName as $r)
        <li class="flex justify-between gap-3 py-2"><span class="text-gray-700 dark:text-gray-300">{!! $dot($r->site) !!}{{ $name($r->site) }} · {{ $labels[$r->name] ?? $r->name }}</span><b class="text-gray-900 dark:text-white">{{ number_format($r->n) }}</b></li>
      @empty
        <li class="py-2 text-gray-500">No conversions in this period. Names counted: {{ implode(', ', array_keys($labels)) }}.</li>
      @endforelse
    </ul>
  </x-ui.card>

  <x-ui.card class="xl:col-span-2" title="By landing page" subtitle="Where converting visits started (first page of the visit / first touch of the session)" :padded="false">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead class="border-y border-gray-100 dark:border-gray-800"><tr><th class="{{ $th }}">Site</th><th class="{{ $th }}">Landing page</th><th class="{{ $th }} text-right">Visits</th><th class="{{ $th }} text-right">Conversions</th><th class="{{ $th }} text-right">Rate</th></tr></thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
          @forelse ($byLanding as $r)
            <tr><td class="{{ $td }}">{!! $dot($r->site) !!}{{ $name($r->site) }}</td><td class="{{ $td }} font-medium text-gray-900 dark:text-white">{{ $r->path }}</td><td class="{{ $td }} text-right">{{ number_format($r->visits) }}</td><td class="{{ $td }} text-right font-medium">{{ $r->conversions }}</td><td class="{{ $td }} text-right">{{ $pct($r->rate) }}</td></tr>
          @empty
            <tr><td colspan="5" class="{{ $td }} text-gray-500">No visits in this period yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </x-ui.card>
</div>

<x-ui.card title="By source, medium and campaign" subtitle="utm_source / utm_medium / utm_campaign when the link was tagged, otherwise the referring site or direct. Tag your links with the UTM builder in the Ad center." :padded="false">
  <div class="overflow-x-auto">
    <table class="w-full text-left text-sm">
      <thead class="border-y border-gray-100 dark:border-gray-800"><tr><th class="{{ $th }}">Site</th><th class="{{ $th }}">Source</th><th class="{{ $th }}">Medium</th><th class="{{ $th }}">Campaign</th><th class="{{ $th }}">Channel</th><th class="{{ $th }} text-right">Visits</th><th class="{{ $th }} text-right">Conversions</th><th class="{{ $th }} text-right">Rate</th></tr></thead>
      <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
        @forelse ($bySource as $r)
          <tr><td class="{{ $td }}">{!! $dot($r->site) !!}{{ $name($r->site) }}</td><td class="{{ $td }} font-medium text-gray-900 dark:text-white">{{ $r->source }}</td><td class="{{ $td }}">{{ $r->medium ?: '—' }}</td><td class="{{ $td }} text-xs">{{ $r->campaign ?: '—' }}</td><td class="{{ $td }} text-xs">{{ \App\Support\Growth\Analytics::CHANNELS[$r->channel] ?? $r->channel }}</td><td class="{{ $td }} text-right">{{ number_format($r->visits) }}</td><td class="{{ $td }} text-right font-medium">{{ $r->conversions }}</td><td class="{{ $td }} text-right">{{ $pct($r->rate) }}</td></tr>
        @empty
          <tr><td colspan="8" class="{{ $td }} text-gray-500">No visits in this period yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</x-ui.card>
@endsection
