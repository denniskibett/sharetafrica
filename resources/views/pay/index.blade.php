{{-- Transactions: every payment in and out, listed as in the mobile app (standard icon per kind, signed amount).
     A row opens the receipt panel; starting a payment happens in the dashboard's money panel. --}}
@extends('layouts.app')
@section('title', 'Transactions')

@section('content')
<div x-data="{
       rows: [], stats: {}, page: 1, last: 1, filter: '', loading: true,
       async load(more = false) {
         this.page = more ? this.page + 1 : 1; this.loading = true;
         const q = new URLSearchParams({ per_page: 20, page: this.page, sort: '-date', ...(this.filter && { status: this.filter }) });
         const r = await (await fetch('{{ route('pay.index') }}?' + q, { headers: { Accept: 'application/json' } })).json();
         this.rows = more ? this.rows.concat(r.data) : r.data; this.stats = r.stats; this.last = r.meta.last_page; this.loading = false;
       },
     }" x-init="load()" @wallet-changed.window="load()" class="flex flex-col gap-6">
  <div>
    <h1>Transactions</h1>
    <p class="mt-1 text-sm text-app-muted">Everything in and out of your wallet.</p>
  </div>

  {{-- Big numbers, then a second line: what it is made of, and last month to compare --}}
  <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
    @php
      $cards = [
        ['Wallet', 'wallet', 'wallet', "stats.waiting ? stats.waiting + ' on the way' : 'All settled'"],
        ['In this month', 'month_in', 'arrow-down-left', "(stats.month_in_count ?? 0) + ' in · last month ' + (stats.last_in ?? '0.00')"],
        ['Out this month', 'month_out', 'arrow-up-right', "(stats.month_out_count ?? 0) + ' out · last month ' + (stats.last_out ?? '0.00')"],
        ['Fees this month', 'month_fees', 'receipt-percent', "'On ' + (stats.month_out_count ?? 0) + ' payments · free up to 100'"],
      ];
    @endphp
    @foreach ($cards as [$label, $key, $icon, $second])
      <div class="rounded-2xl border border-app-line bg-app-panel p-4 md:p-5">
        <div class="flex items-center justify-between gap-2">
          <p class="text-sm text-app-muted">{{ $label }}</p>
          <span @class(['flex h-9 w-9 items-center justify-center rounded-full', 'bg-app-in/15 text-app-in' => $key === 'month_in', 'bg-orange-500/15 text-orange-600 dark:text-orange-300' => $key === 'month_out', 'bg-app-lime text-[#050507]' => $key === 'wallet', 'bg-app-tile text-app-muted' => $key === 'month_fees'])><x-dynamic-component :component="'heroicon-o-'.$icon" class="h-5 w-5" /></span>
        </div>
        <p @class(['mt-2 truncate text-[26px] font-bold leading-tight tabular-nums md:text-[30px]', 'text-app-in' => $key === 'month_in', 'text-app-ink' => $key === 'month_out'])>
          <span class="text-sm font-semibold text-app-muted">KES</span> <span x-text="stats.{{ $key }} ?? '—'"></span>
        </p>
        <p class="mt-1 truncate text-xs text-app-muted" x-text="{{ $second }}"></p>
      </div>
    @endforeach
  </div>

  @include('pay.partials.chart')

  <section class="order-2 rounded-2xl border border-app-line bg-app-panel p-5">
    <div class="mb-2 flex flex-wrap items-center justify-between gap-3">
      <h2 class="text-base font-semibold">All transactions</h2>
      <div class="flex flex-wrap items-center gap-2">
      <div class="flex gap-1 rounded-full bg-app-tile p-1 text-sm" role="tablist">
        @foreach (['' => 'All', 'paid' => 'Done', 'collecting' => 'Waiting', 'refunded' => 'Refunded'] as $value => $text)
          <button type="button" role="tab" @click="filter = '{{ $value }}'; load()" :aria-selected="filter === '{{ $value }}'"
                  :class="filter === '{{ $value }}' ? 'bg-app-panel text-app-ink shadow-sm' : 'text-app-muted'" class="h-8 rounded-full px-3 font-medium">{{ $text }}</button>
        @endforeach
      </div>
      <a href="{{ route('dashboard', ['panel' => 'send']) }}" class="flex h-10 items-center gap-2 rounded-full bg-app-lime px-4 text-sm font-semibold text-[#050507]"><x-heroicon-o-arrow-up-right class="h-5 w-5" /> Send money</a>
      </div>
    </div>
    @if (config('rails.default') === 'simulator')<p class="mb-2 text-xs text-app-muted">Test mode: payments are simulated, no real money moves.</p>@endif
    <ul x-show="rows.length" class="space-y-1.5">
      <template x-for="r in rows" :key="r.id">@include('pay.partials.row')</template>
    </ul>
    <p x-show="!loading && !rows.length" x-cloak class="py-10 text-center text-sm text-app-muted">No transactions here yet.</p>
    <button type="button" x-show="page < last" x-cloak @click="load(true)" :disabled="loading" class="mt-3 h-11 w-full rounded-full bg-app-tile text-sm font-semibold hover:bg-app-raised" x-text="loading ? 'Loading…' : 'Show more'"></button>
  </section>
</div>
@endsection
