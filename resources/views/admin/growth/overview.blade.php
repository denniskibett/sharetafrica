{{-- Growth › Overview: visits, page views, conversions and rate per site, trend, channel mix. --}}
@extends('admin.growth.layout')

@section('growth')
@php
    $th = 'px-3 py-2 text-xs font-medium whitespace-nowrap text-gray-500 dark:text-gray-400';
    $td = 'px-3 py-2.5 text-sm';
    $visits = $perSite->sum('visits'); $conv = $perSite->sum('conversions');
    $organicVisits = $channels->where('channel', 'organic')->sum('visits');
    $kpis = [
        ['Visits', number_format($visits), 'One visitor, one site, one day'],
        ['Page views', number_format($perSite->sum('views')), $visits ? round($perSite->sum('views') / $visits, 1).' per visit' : '—'],
        ['Conversions', number_format($conv), 'Sign-ups, leads, purchases…'],
        ['Conversion rate', $visits ? round($conv / $visits * 100, 2).'%' : '—', 'Conversions ÷ visits'],
        ['Organic search', $visits ? round($organicVisits / $visits * 100).'%' : '—', 'Of visits'],
        ['Sites sending data', $perSite->filter(fn ($s) => $s->last_seen)->count().' of '.$perSite->count(), 'Ever seen by the collector'],
    ];
    $max = max(1, $series->max('views'));
@endphp

<div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-6">
  @foreach ($kpis as [$label, $value, $hint])
    <div class="ui-card rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
      <p class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</p>
      <p class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $value }}</p>
      <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</p>
    </div>
  @endforeach
</div>

<x-ui.card class="mb-6" :title="$days > 90 ? 'Per week' : 'Per day'" subtitle="Bars: page views · lighter bars: visits · red dots: conversions">
  <div role="img" aria-label="{{ $series->sum('views') }} page views and {{ $series->sum('conversions') }} conversions in the period" class="flex h-48 items-end gap-0.5 border-b border-gray-200 dark:border-gray-800">
    @foreach ($series as $p)
      <div title="{{ \Illuminate\Support\Carbon::parse($p['date'])->format('j M') }}: {{ $p['views'] }} views, {{ $p['visits'] }} visits, {{ $p['conversions'] }} conversions" class="relative flex h-full min-w-0 flex-1 items-end justify-center">
        <div class="w-full rounded-t bg-gray-800/70 dark:bg-white/60" style="height: {{ round($p['views'] / $max * 100, 1) }}%"></div>
        <div class="absolute bottom-0 w-1/2 rounded-t bg-emerald-400/80" style="height: {{ round($p['visits'] / $max * 100, 1) }}%"></div>
        @if ($p['conversions'])<span class="absolute h-2 w-2 rounded-full bg-red-600" style="bottom: calc({{ round($p['views'] / $max * 100, 1) }}% + 4px)"></span>@endif
      </div>
    @endforeach
  </div>
  <div class="mt-1 flex justify-between text-xs text-gray-400"><span>{{ \Illuminate\Support\Carbon::parse($series->first()['date'])->format('j M') }}</span><span>{{ \Illuminate\Support\Carbon::parse($series->last()['date'])->format('j M') }}</span></div>
</x-ui.card>

<div class="grid gap-6 xl:grid-cols-3">
  <x-ui.card class="xl:col-span-2" title="Per site" subtitle="Last data = the newest beacon ever received from that site (installed or not)." :padded="false">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead class="border-y border-gray-100 dark:border-gray-800"><tr><th class="{{ $th }}">Site</th><th class="{{ $th }} text-right">Visits</th><th class="{{ $th }} text-right">Page views</th><th class="{{ $th }} text-right">Conversions</th><th class="{{ $th }} text-right">Rate</th><th class="{{ $th }} text-right">Organic</th><th class="{{ $th }}">Last data</th></tr></thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
          @foreach ($perSite as $s)
            <tr>
              <td class="{{ $td }}"><a href="{{ route('admin.growth.index', array_filter(['site' => $s->key, 'days' => $days === 30 ? null : $days])) }}" class="inline-flex items-center gap-2 font-medium text-gray-900 hover:underline dark:text-white"><span class="h-2.5 w-2.5 rounded-full" style="background: {{ $s->color }}"></span>{{ $s->name }}</a><br><span class="text-xs text-gray-500">{{ $s->product }}</span></td>
              <td class="{{ $td }} text-right">{{ number_format($s->visits) }}</td>
              <td class="{{ $td }} text-right">{{ number_format($s->views) }}</td>
              <td class="{{ $td }} text-right font-medium">{{ number_format($s->conversions) }}</td>
              <td class="{{ $td }} text-right">{{ $s->rate !== null ? $s->rate.'%' : '—' }}</td>
              <td class="{{ $td }} text-right">{{ $s->organic !== null ? $s->organic.'%' : '—' }}</td>
              <td class="{{ $td }} text-xs">@if ($s->last_seen)<span @class(['text-emerald-700 dark:text-emerald-400' => $s->last_seen->gt(now()->subDay()), 'text-amber-700' => $s->last_seen->lte(now()->subDay())])>{{ $s->last_seen->diffForHumans() }}</span>@else<span class="text-red-600">Never: tracker not installed</span>@endif</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </x-ui.card>

  <x-ui.card title="Where visits come from" subtitle="Channel of each visit's first page view">
    @php $chTotal = max(1, $channels->sum('visits')); @endphp
    <ul class="space-y-3 text-sm">
      @forelse ($channels as $c)
        <li>
          <div class="flex justify-between gap-3"><span class="text-gray-700 dark:text-gray-300">{{ \App\Support\Growth\Analytics::CHANNELS[$c->channel] ?? $c->channel }}</span><span class="text-gray-900 dark:text-white">{{ number_format($c->visits) }} · {{ $c->conversions }} conv.</span></div>
          <div class="mt-1 h-1.5 rounded-full bg-gray-100 dark:bg-white/10"><div class="h-1.5 rounded-full bg-emerald-500" style="width: {{ round($c->visits / $chTotal * 100) }}%"></div></div>
        </li>
      @empty
        <li class="text-gray-500">No visits in this period yet.</li>
      @endforelse
    </ul>
  </x-ui.card>
</div>
@endsection
