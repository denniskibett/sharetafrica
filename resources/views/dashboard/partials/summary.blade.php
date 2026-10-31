{{-- Under the wallet: this month in and out, fees paid, today's use of the daily limit, and people paid recently
     ("send again" opens the money panel filled in). Needs the dashboard's Alpine `stats` (pay.index JSON). --}}
<section class="rounded-2xl border border-app-line bg-app-panel p-5">
  <h2 class="mb-4 text-base font-semibold">This month</h2>
  <div class="grid grid-cols-3 gap-3">
    @foreach ([['Money in', 'month_in', 'arrow-down-left'], ['Money out', 'month_out', 'arrow-up-right'], ['Fees paid', 'month_fees', 'receipt-percent']] as [$label, $key, $icon])
      <div class="rounded-xl bg-app-tile p-3">
        <x-dynamic-component :component="'heroicon-o-'.$icon" @class(['h-5 w-5', 'text-app-in' => $key === 'month_in', 'text-app-out' => $key === 'month_out', 'text-app-muted' => ! in_array($key, ['month_in', 'month_out'])]) />
        <p class="mt-2 text-xs text-app-muted">{{ $label }}</p>
        <p class="truncate text-[15px] tabular-nums" @class(['text-app-in' => $key === 'month_in', 'text-app-out' => $key === 'month_out']) x-text="stats.{{ $key }} ?? '—'"></p>
      </div>
    @endforeach
  </div>

  <div class="mt-5">
    <div class="flex items-baseline justify-between text-sm">
      <span class="flex items-center gap-2 text-app-soft"><x-heroicon-o-shield-check class="h-5 w-5" /> Today's limit</span>
      <span class="app-mono text-xs tabular-nums text-app-muted" x-text="(stats.today_used ?? '0.00') + ' of ' + (stats.per_day ?? '—')"></span>
    </div>
    <div class="mt-2 h-1.5 rounded-full bg-app-tile"><div class="h-1.5 rounded-full bg-app-accent" :style="`width: ${stats.today_percent ?? 0}%`"></div></div>
    <p class="mt-2 text-xs text-app-muted" x-text="'Up to KES ' + (stats.per_order ?? '—') + ' per payment.'"></p>
  </div>

  <div class="mt-5" x-show="stats.recent?.length" x-cloak>
    <p class="mb-2 text-sm text-app-soft">Send again</p>
    <ul class="grid gap-2 sm:grid-cols-2">
      <template x-for="(p, i) in stats.recent" :key="i">
        <li><button type="button" @click="sharet.panel('money', p.payload)" class="flex w-full items-center gap-3 rounded-xl bg-app-tile px-3 py-2.5 text-left text-sm hover:bg-app-raised">
          <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-app-panel">@include('pay.partials.icon', ['expr' => 'p'])</span>
          <span class="min-w-0 truncate" x-text="p.label"></span>
        </button></li>
      </template>
    </ul>
  </div>
</section>
