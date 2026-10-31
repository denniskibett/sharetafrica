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

  <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
    @foreach ([['Wallet', 'wallet', 'wallet'], ['In this month', 'month_in', 'arrow-down-left'], ['Out this month', 'month_out', 'arrow-up-right'], ['Fees this month', 'month_fees', 'receipt-percent']] as [$label, $key, $icon])
      <div class="rounded-2xl border border-app-line bg-app-panel p-4">
        <x-dynamic-component :component="'heroicon-o-'.$icon" @class(['h-5 w-5', 'text-app-in' => $key === 'month_in', 'text-app-out' => $key === 'month_out', 'text-app-muted' => ! in_array($key, ['month_in', 'month_out'])]) />
        <p class="mt-2 text-xs text-app-muted">{{ $label }} · KES</p>
        <p class="text-lg tabular-nums" @class(['text-app-in' => $key === 'month_in', 'text-app-out' => $key === 'month_out']) x-text="stats.{{ $key }} ?? '—'"></p>
      </div>
    @endforeach
  </div>

  @include('pay.partials.chart')

  <section class="order-2 rounded-2xl border border-app-line bg-app-panel p-5">
    <div class="mb-2 flex flex-wrap items-center justify-between gap-3">
      <h2 class="text-base font-semibold">All transactions</h2>
      <div class="flex flex-wrap items-center gap-2">
      <div class="flex gap-1 rounded-lg bg-app-tile p-1 text-sm" role="tablist">
        @foreach (['' => 'All', 'paid' => 'Done', 'collecting' => 'Waiting', 'refunded' => 'Refunded'] as $value => $text)
          <button type="button" role="tab" @click="filter = '{{ $value }}'; load()" :aria-selected="filter === '{{ $value }}'"
                  :class="filter === '{{ $value }}' ? 'bg-app-panel text-app-ink shadow-sm' : 'text-app-muted'" class="h-8 rounded-md px-3 font-medium">{{ $text }}</button>
        @endforeach
      </div>
      <a href="{{ route('dashboard', ['panel' => 'send']) }}" class="flex h-10 items-center gap-2 rounded-xl bg-app-lime px-4 text-sm font-semibold text-[#050507] hover:bg-[#E2FF73]"><x-heroicon-o-paper-airplane class="h-5 w-5" /> Send money</a>
      </div>
    </div>
    @if (config('rails.default') === 'simulator')<p class="mb-2 text-xs text-app-muted">Test mode: payments are simulated, no real money moves.</p>@endif
    <ul x-show="rows.length">
      <template x-for="r in rows" :key="r.id">@include('pay.partials.row')</template>
    </ul>
    <p x-show="!loading && !rows.length" x-cloak class="py-10 text-center text-sm text-app-muted">No transactions here yet.</p>
    <button type="button" x-show="page < last" x-cloak @click="load(true)" :disabled="loading" class="mt-3 h-11 w-full rounded-xl bg-app-tile text-sm font-medium hover:bg-app-raised" x-text="loading ? 'Loading…' : 'Show more'"></button>
  </section>
</div>
@endsection
