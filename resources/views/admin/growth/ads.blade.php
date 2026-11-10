{{-- Growth › Ad center: what to push (computed from the data, rules shown), budget split, ad copy per product, UTM link builder. --}}
@extends('admin.growth.layout')

@section('growth')
@php
    $th = 'px-3 py-2 text-xs font-medium whitespace-nowrap text-gray-500 dark:text-gray-400';
    $td = 'px-3 py-2.5 text-sm';
    $name = fn ($k) => $sites[$k]['name'] ?? $k;
    $level = ['push' => ['Push', 'bg-emerald-600 text-white'], 'fix' => ['Fix', 'bg-amber-400 text-gray-900'], 'test' => ['Test', 'bg-sky-600 text-white'], 'info' => ['Note', 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-200']];
    $kes = fn ($n) => 'KES '.number_format($n);
@endphp

<div class="mb-6 grid gap-6 xl:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]">
  <x-ui.card title="What to push" subtitle="{{ number_format($totals['visits']) }} visits and {{ number_format($totals['conversions']) }} conversions in this period{{ $totals['enough'] ? '' : ' — not enough to rank yet' }}">
    <ul class="divide-y divide-gray-100 dark:divide-gray-800">
      @forelse ($recommendations as [$lvl, $title, $detail])
        <li class="flex gap-3 py-3 text-sm">
          <span class="h-fit shrink-0 rounded-full px-2 py-0.5 text-[11px] font-semibold uppercase {{ $level[$lvl][1] }}">{{ $level[$lvl][0] }}</span>
          <span class="min-w-0"><span class="font-medium text-gray-900 dark:text-white">{{ $title }}</span><br><span class="text-gray-600 dark:text-gray-300">{{ $detail }}</span></span>
        </li>
      @empty
        <li class="py-3 text-sm text-gray-500">Nothing stands out yet.</li>
      @endforelse
    </ul>
  </x-ui.card>

  <x-ui.card title="Organic vs paid" subtitle="Share of visits in this period">
    @if ($ovp['visits'])
      <div class="flex h-4 overflow-hidden rounded-full bg-gray-100 dark:bg-white/10">
        <div class="bg-emerald-500" style="width: {{ $ovp['organic'] }}%" title="Organic {{ $ovp['organic'] }}%"></div>
        <div class="bg-violet-500" style="width: {{ $ovp['paid'] }}%" title="Paid {{ $ovp['paid'] }}%"></div>
      </div>
      <p class="mt-2 text-sm text-gray-700 dark:text-gray-300"><span class="mr-1 inline-block h-2 w-2 rounded-full bg-emerald-500"></span>Organic {{ $ovp['organic'] }}% · <span class="mr-1 inline-block h-2 w-2 rounded-full bg-violet-500"></span>Paid {{ $ovp['paid'] }}% · other {{ $ovp['other'] }}%</p>
    @else
      <p class="text-sm text-gray-500">No visits in this period yet.</p>
    @endif
    <h4 class="mb-2 mt-5 text-sm font-semibold text-gray-900 dark:text-white">Channels by conversion rate</h4>
    <ul class="space-y-1 text-sm">
      @forelse ($channels->sortByDesc(fn ($c) => $c->rate ?? -1) as $c)
        <li class="flex justify-between gap-3 text-gray-700 dark:text-gray-300"><span>{{ \App\Support\Growth\Analytics::CHANNELS[$c->channel] ?? $c->channel }}</span><span>{{ $c->rate !== null ? $c->rate.'%' : '—' }} <span class="text-xs text-gray-500">({{ $c->conversions }}/{{ number_format($c->visits) }})</span></span></li>
      @empty
        <li class="text-gray-500">No channels yet.</li>
      @endforelse
    </ul>
  </x-ui.card>
</div>

<x-ui.card class="mb-6" title="Suggested budget split">
  <x-slot:actions>
    <form method="GET" class="flex items-center gap-2">
      @if ($site)<input type="hidden" name="site" value="{{ $site }}">@endif
      @if ($days !== 30)<input type="hidden" name="days" value="{{ $days }}">@endif
      <label for="budget" class="text-sm text-gray-600 dark:text-gray-400">Monthly budget (KES)</label>
      <input id="budget" name="budget" type="number" min="0" step="1000" value="{{ $budget ?: '' }}" placeholder="e.g. 50000" class="h-9 w-32 rounded-lg border border-gray-300 bg-transparent px-2 text-sm dark:border-gray-700">
      <button class="h-9 rounded-lg bg-gray-900 px-3 text-sm font-medium text-white dark:bg-white dark:text-gray-900">Split</button>
    </form>
  </x-slot:actions>
  <div class="grid gap-6 lg:grid-cols-2">
    <div>
      <h4 class="mb-2 text-sm font-semibold text-gray-900 dark:text-white">Across sites</h4>
      <ul class="space-y-2 text-sm">
        @foreach ($siteSplit as $r)
          <li>
            <div class="flex justify-between gap-3"><span class="text-gray-800 dark:text-gray-200">{{ $r->name }}</span><span class="font-medium text-gray-900 dark:text-white">{{ $r->share }}%@if ($r->amount !== null) · {{ $kes($r->amount) }}@endif</span></div>
            <div class="mt-1 h-1.5 rounded-full bg-gray-100 dark:bg-white/10"><div class="h-1.5 rounded-full" style="width: {{ $r->share }}%; background: {{ $r->color }}"></div></div>
            <p class="mt-0.5 text-xs text-gray-500">{{ $r->why }}</p>
          </li>
        @endforeach
      </ul>
      <p class="mt-3 text-xs text-gray-500 dark:text-gray-400"><b>Rule:</b> {{ $totals['enough'] ? $rules['sites'] : $rules['fallback'] }}</p>
    </div>
    <div>
      <h4 class="mb-2 text-sm font-semibold text-gray-900 dark:text-white">Across channels</h4>
      <ul class="space-y-2 text-sm">
        @foreach ($channelSplit as $r)
          <li>
            <div class="flex justify-between gap-3"><span class="text-gray-800 dark:text-gray-200">{{ $r->name }}</span><span class="font-medium text-gray-900 dark:text-white">{{ $r->share }}%@if ($r->amount !== null) · {{ $kes($r->amount) }}@endif</span></div>
            <div class="mt-1 h-1.5 rounded-full bg-gray-100 dark:bg-white/10"><div class="h-1.5 rounded-full bg-violet-500" style="width: {{ $r->share }}%"></div></div>
            <p class="mt-0.5 text-xs text-gray-500">{{ $r->from_data ? number_format($r->visits).' visits, '.$r->conversions.' conversions'.($r->rate !== null ? ' ('.$r->rate.'%)' : '') : 'Default split: not enough data from this channel yet' }}</p>
          </li>
        @endforeach
      </ul>
      <p class="mt-3 text-xs text-gray-500 dark:text-gray-400"><b>Rule:</b> {{ $channelSplit->first()?->from_data ? $rules['channels'] : $rules['fallback'] }}</p>
    </div>
  </div>
</x-ui.card>

<div class="mb-6 grid gap-6 xl:grid-cols-2">
  <x-ui.card title="Top converting combinations" subtitle="Site + landing page + source with 2 or more conversions" :padded="false">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead class="border-y border-gray-100 dark:border-gray-800"><tr><th class="{{ $th }}">Site</th><th class="{{ $th }}">Landing page</th><th class="{{ $th }}">Source</th><th class="{{ $th }} text-right">Conv.</th><th class="{{ $th }} text-right">Rate</th></tr></thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
          @forelse ($combos as $c)
            <tr><td class="{{ $td }}">{{ $name($c->site) }}</td><td class="{{ $td }} font-medium text-gray-900 dark:text-white">{{ $c->path }}</td><td class="{{ $td }}">{{ $c->source }}</td><td class="{{ $td }} text-right font-medium">{{ $c->conversions }}</td><td class="{{ $td }} text-right">{{ $c->rate !== null ? $c->rate.'%' : '—' }}</td></tr>
          @empty
            <tr><td colspan="5" class="{{ $td }} text-gray-500">Not enough data: no combination has 2 conversions yet.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </x-ui.card>

  <x-ui.card title="Traffic but no conversions" subtitle="Pages with 30+ views where no conversion started or happened" :padded="false">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead class="border-y border-gray-100 dark:border-gray-800"><tr><th class="{{ $th }}">Site</th><th class="{{ $th }}">Page</th><th class="{{ $th }} text-right">Views</th><th class="{{ $th }} text-right">Avg. time</th></tr></thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
          @forelse ($leaky as $p)
            <tr><td class="{{ $td }}">{{ $name($p->site) }}</td><td class="{{ $td }} font-medium text-gray-900 dark:text-white">{{ $p->path }}</td><td class="{{ $td }} text-right">{{ number_format($p->views) }}</td><td class="{{ $td }} text-right">{{ $p->avg_ms ? round($p->avg_ms / 1000).'s' : '—' }}</td></tr>
          @empty
            <tr><td colspan="4" class="{{ $td }} text-gray-500">None: no page has 30+ views without a conversion.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </x-ui.card>
</div>

{{-- UTM link builder: tag every link you share so the hub can tell which ad, post or message brought the visit --}}
<x-ui.card class="mb-6" title="UTM link builder" subtitle="Tag every ad, post and message link. Use lower case and underscores; never put names or phone numbers in a tag.">
  <div x-data="{
        sites: @js(collect($sites)->map(fn ($s) => $s['url'])),
        site: @js($site ?? array_key_first($sites)), path: '/', source: 'google', medium: 'cpc', campaign: '', content: '', copied: false,
        presets: { google: 'cpc', facebook: 'paid_social', instagram: 'paid_social', tiktok: 'paid_social', whatsapp: 'social', sms: 'sms', newsletter: 'email' },
        slug(v) { return (v || '').toLowerCase().trim().replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, '') },
        get link() {
          const u = new URL((this.path || '/').startsWith('/') ? this.path || '/' : '/' + this.path, this.sites[this.site]);
          [['utm_source', this.source], ['utm_medium', this.medium], ['utm_campaign', this.campaign], ['utm_content', this.content]]
            .forEach(([k, v]) => { v = this.slug(v); if (v) u.searchParams.set(k, v) });
          return u.toString();
        },
        copy() { navigator.clipboard.writeText(this.link); this.copied = true; setTimeout(() => this.copied = false, 1500) }
      }">
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-6">
      <label class="text-xs text-gray-500">Site<select x-model="site" class="mt-1 h-9 w-full rounded-lg border border-gray-300 bg-transparent px-2 text-sm text-gray-900 dark:border-gray-700 dark:text-white">@foreach ($sites as $k => $s)<option value="{{ $k }}">{{ $s['name'] }}</option>@endforeach</select></label>
      <label class="text-xs text-gray-500">Landing page<input x-model="path" class="mt-1 h-9 w-full rounded-lg border border-gray-300 bg-transparent px-2 text-sm text-gray-900 dark:border-gray-700 dark:text-white" placeholder="/"></label>
      <label class="text-xs text-gray-500">Source<select x-model="source" @change="medium = presets[source] || medium" class="mt-1 h-9 w-full rounded-lg border border-gray-300 bg-transparent px-2 text-sm text-gray-900 dark:border-gray-700 dark:text-white"><template x-for="(m, s) in presets" :key="s"><option :value="s" x-text="s"></option></template></select></label>
      <label class="text-xs text-gray-500">Medium<input x-model="medium" class="mt-1 h-9 w-full rounded-lg border border-gray-300 bg-transparent px-2 text-sm text-gray-900 dark:border-gray-700 dark:text-white"></label>
      <label class="text-xs text-gray-500">Campaign<input x-model="campaign" class="mt-1 h-9 w-full rounded-lg border border-gray-300 bg-transparent px-2 text-sm text-gray-900 dark:border-gray-700 dark:text-white" placeholder="nairobi_events_dec"></label>
      <label class="text-xs text-gray-500">Content (optional)<input x-model="content" class="mt-1 h-9 w-full rounded-lg border border-gray-300 bg-transparent px-2 text-sm text-gray-900 dark:border-gray-700 dark:text-white" placeholder="video_a"></label>
    </div>
    <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center">
      <code class="min-w-0 flex-1 break-all rounded-lg bg-gray-50 px-3 py-2 text-xs text-gray-800 dark:bg-white/5 dark:text-gray-200" x-text="link"></code>
      <button type="button" @click="copy()" class="h-9 shrink-0 rounded-lg border border-gray-300 px-3 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5" x-text="copied ? 'Copied' : 'Copy link'"></button>
    </div>
  </div>
</x-ui.card>

<h3 class="mb-3 text-[17px] font-semibold text-gray-800 dark:text-white/90">Ad copy, ready to use</h3>
<p class="mb-4 text-sm text-gray-500 dark:text-gray-400">Per product, for its Kenyan search intent. Google headlines fit 30 characters and descriptions 90. No prices, rates or promises the products do not make: adjust before you publish.</p>
<div class="grid gap-6 xl:grid-cols-2">
  @foreach ($copy as $key => $c)
    <x-ui.card :title="$name($key)" :subtitle="$sites[$key]['product']" x-data="{ copied: '' }">
      <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Google Search · keywords</h4>
      <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ implode(' · ', $c['keywords']) }}</p>
      <h4 class="mt-4 text-xs font-semibold uppercase tracking-wide text-gray-500">Headlines (≤30)</h4>
      <ul class="mt-1 flex flex-wrap gap-1.5">@foreach ($c['headlines'] as $h)<li class="rounded-lg bg-gray-100 px-2 py-1 text-xs text-gray-800 dark:bg-white/10 dark:text-gray-200">{{ $h }} <span class="text-gray-400">{{ mb_strlen($h) }}</span></li>@endforeach</ul>
      <h4 class="mt-4 text-xs font-semibold uppercase tracking-wide text-gray-500">Descriptions (≤90)</h4>
      <ul class="mt-1 space-y-1 text-sm text-gray-700 dark:text-gray-300">@foreach ($c['descriptions'] as $d)<li>{{ $d }} <span class="text-xs text-gray-400">{{ mb_strlen($d) }}</span></li>@endforeach</ul>
      @foreach (['meta' => 'Meta (Facebook / Instagram)', 'whatsapp' => 'WhatsApp (add your tagged link)', 'tiktok' => 'TikTok caption'] as $k => $label)
        <div class="mt-4 flex items-center justify-between gap-2">
          <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $label }}</h4>
          <button type="button" class="text-xs font-medium text-gray-600 hover:underline dark:text-gray-300" @click="navigator.clipboard.writeText(@js($c[$k])); copied = '{{ $k }}'; setTimeout(() => copied = '', 1500)" x-text="copied === '{{ $k }}' ? 'Copied' : 'Copy'"></button>
        </div>
        <p class="mt-1 text-sm text-gray-700 dark:text-gray-300">{{ $c[$k] }}</p>
      @endforeach
    </x-ui.card>
  @endforeach
</div>
@endsection
