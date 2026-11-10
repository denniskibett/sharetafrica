{{-- Growth › SEO: the latest audit per site (php artisan growth:seo-audit, daily 05:30), organic share and the owner's to-do list. --}}
@extends('admin.growth.layout')

@section('growth')
@php
    $th = 'px-3 py-2 text-xs font-medium whitespace-nowrap text-gray-500 dark:text-gray-400';
    $td = 'px-3 py-2 text-sm align-top';
    $pill = ['red' => 'bg-red-600 text-white', 'amber' => 'bg-amber-400 text-gray-900', 'green' => 'bg-emerald-600 text-white'];
    $order = ['red' => 0, 'amber' => 1, 'green' => 2];
@endphp

<p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
  @if ($checkedAt) Last audit {{ \Illuminate\Support\Carbon::parse($checkedAt)->diffForHumans() }}. @else No audit yet. @endif
  Runs every morning at 05:30; run it now from <a href="{{ route('admin.scheduler.index') }}" class="font-medium text-gray-800 underline dark:text-gray-200">Scheduled jobs</a> or <code>php artisan growth:seo-audit</code>. Checks the home page, robots.txt, sitemap.xml and {{ config('growth.audit_sample') }} sitemap pages per site.
</p>

@foreach ($keys as $key)
  @php
      $s = $sites[$key]; $rows = $checks[$key] ?? collect(); $all = $rows->flatMap->findings;
      $counts = ['red' => $all->where('level', 'red')->count(), 'amber' => $all->where('level', 'amber')->count(), 'green' => $all->where('level', 'green')->count()];
      $ps = $perSite[$key] ?? null; $sitemap = $rows->firstWhere('kind', 'sitemap');
      $todo = $todos[$key];
  @endphp
  <x-ui.card class="mb-6" :padded="false">
    <div class="flex flex-col gap-3 px-5 pt-5 sm:flex-row sm:items-start sm:justify-between sm:px-6 sm:pt-6">
      <div>
        <h3 class="flex items-center gap-2 text-[17px] font-semibold text-gray-800 dark:text-white/90"><span class="h-3 w-3 rounded-full" style="background: {{ $s['color'] }}"></span>{{ $s['name'] }} <a href="{{ $s['url'] }}" target="_blank" rel="noopener" class="text-sm font-normal text-gray-500 hover:underline">{{ parse_url($s['url'], PHP_URL_HOST) }}</a></h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $s['product'] }} · organic search {{ $ps && $ps->organic !== null ? $ps->organic.'% of '.number_format($ps->visits).' visits' : 'no visits in this period' }} · sitemap {{ $sitemap && $sitemap->sitemap_urls !== null ? number_format($sitemap->sitemap_urls).' URLs' : '—' }}</p>
      </div>
      <div class="flex gap-1.5">
        @foreach ($counts as $lvl => $n)<span class="whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $pill[$lvl] }}">{{ $n }} {{ $lvl }}</span>@endforeach
      </div>
    </div>

    <div class="grid gap-6 p-5 sm:p-6 xl:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]">
      <div class="min-w-0">
        @if ($rows->isEmpty())
          <p class="text-sm text-gray-500">Not audited yet.</p>
        @else
          <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-800">
            <table class="w-full text-left text-sm">
              <thead class="border-b border-gray-100 dark:border-gray-800"><tr><th class="{{ $th }}">URL</th><th class="{{ $th }}">Status</th><th class="{{ $th }}">Title / desc.</th><th class="{{ $th }}">h1</th><th class="{{ $th }}">Canonical · OG · JSON-LD</th><th class="{{ $th }}">Findings</th></tr></thead>
              <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach ($rows as $c)
                  @php $worst = $c->findings->sortBy(fn ($f) => $order[$f['level']] ?? 3)->first()['level'] ?? 'green'; @endphp
                  <tr x-data="{ open: false }">
                    <td class="{{ $td }} max-w-[16rem]"><span class="block truncate font-medium text-gray-900 dark:text-white" title="{{ $c->url }}">{{ $c->kind === 'home' ? '/' : (parse_url($c->url, PHP_URL_PATH) ?: $c->url) }}</span><span class="text-xs text-gray-500">{{ $c->kind }}{{ $c->response_ms !== null ? ' · '.$c->response_ms.' ms' : '' }}</span></td>
                    <td class="{{ $td }}"><span @class(['font-semibold', 'text-emerald-700' => $c->status === 200, 'text-red-600' => $c->status !== 200])>{{ $c->status ?? 'none' }}</span>@if ($c->noindex)<br><span class="text-xs font-semibold text-red-600">noindex</span>@endif</td>
                    <td class="{{ $td }} text-xs">@if ($c->kind === 'home' || $c->kind === 'page'){{ $c->title ? mb_strlen($c->title).' ch' : 'no title' }} · {{ $c->description_length !== null ? $c->description_length.' ch' : 'no desc.' }}@else — @endif</td>
                    <td class="{{ $td }}">{{ $c->h1_count ?? '—' }}</td>
                    <td class="{{ $td }} text-xs">@if ($c->kind === 'home' || $c->kind === 'page'){{ $c->canonical ? '✓' : '✗' }} · {{ $c->og_tags ? count(explode(',', $c->og_tags)) : 0 }} OG · {{ $c->jsonld_types ?: 'none' }}@else — @endif</td>
                    <td class="{{ $td }}"><button type="button" @click="open = !open" class="rounded-full px-2 py-0.5 text-[11px] font-semibold uppercase {{ $pill[$worst] ?? '' }}">{{ $worst }} · {{ $c->findings->count() }}</button>
                      <ul x-show="open" x-cloak class="mt-2 space-y-1 text-xs">
                        @foreach ($c->findings->sortBy(fn ($f) => $order[$f['level']] ?? 3) as $f)<li class="flex gap-1.5"><span class="mt-1 h-2 w-2 shrink-0 rounded-full {{ ['red' => 'bg-red-600', 'amber' => 'bg-amber-400', 'green' => 'bg-emerald-600'][$f['level']] ?? '' }}"></span>{{ $f['text'] }}</li>@endforeach
                      </ul>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          @if ($counts['red'] + $counts['amber'])
            <h4 class="mb-2 mt-4 text-sm font-semibold text-gray-900 dark:text-white">Fix first</h4>
            <ul class="space-y-1 text-sm">
              @foreach ($rows->flatMap(fn ($c) => $c->findings->whereIn('level', ['red', 'amber'])->map(fn ($f) => $f + ['url' => $c->kind === 'home' ? '/' : (parse_url($c->url, PHP_URL_PATH) ?: $c->url)]))->sortBy(fn ($f) => $order[$f['level']])->take(8) as $f)
                <li class="flex gap-2"><span class="h-fit shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase {{ $pill[$f['level']] }}">{{ $f['level'] }}</span><span class="text-gray-700 dark:text-gray-300"><b class="font-medium">{{ $f['url'] }}</b> {{ $f['text'] }}</span></li>
              @endforeach
            </ul>
          @endif
        @endif
      </div>

      <div class="min-w-0">
        <h4 class="mb-2 text-sm font-semibold text-gray-900 dark:text-white">Owner to-do</h4>
        <ul class="divide-y divide-gray-100 rounded-xl border border-gray-100 dark:divide-gray-800 dark:border-gray-800">
          @foreach ($todo as $t)
            <li class="p-3">
              <form method="POST" action="{{ route('admin.growth.todo') }}" class="flex flex-col gap-2">
                @csrf
                <input type="hidden" name="site" value="{{ $key }}"><input type="hidden" name="item" value="{{ $t->item }}">
                <input type="hidden" name="back_site" value="{{ $site }}"><input type="hidden" name="back_days" value="{{ $days === 30 ? '' : $days }}">
                <label class="flex items-start gap-2 text-sm">
                  <input type="hidden" name="done" value="0">
                  <input type="checkbox" name="done" value="1" @checked($t->done) onchange="this.form.requestSubmit()" class="mt-0.5 h-4 w-4 rounded border-gray-300">
                  <span><span @class(['font-medium', 'text-gray-400 line-through' => $t->done, 'text-gray-900 dark:text-white' => ! $t->done])>{{ $t->label }}</span>
                    @if ($t->item === 'tracker')<span class="ml-1 text-xs {{ $ps && $ps->last_seen ? 'text-emerald-700' : 'text-red-600' }}">{{ $ps && $ps->last_seen ? 'data '.$ps->last_seen->diffForHumans() : 'no data yet' }}</span>@endif
                    <br><span class="text-xs text-gray-500 dark:text-gray-400">{{ $t->hint }}</span></span>
                </label>
                <div class="flex gap-2 pl-6">
                  <input type="text" name="note" value="{{ $t->note }}" maxlength="255" placeholder="Note (optional)" class="h-8 min-w-0 flex-1 rounded-lg border border-gray-300 bg-transparent px-2 text-xs dark:border-gray-700">
                  <button class="h-8 rounded-lg border border-gray-300 px-3 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">Save</button>
                </div>
              </form>
            </li>
          @endforeach
        </ul>
      </div>
    </div>
  </x-ui.card>
@endforeach
@endsection
