{{-- One transaction on one line, tinted in its colour (in green, out red, waiting orange, didn't move grey): icon,
     what it was with from → to, date, status and the signed amount. Needs Alpine `r` (RampOrder::present()); opens the
     receipt. Phones keep the same line and drop the date and status columns. --}}
@php
    $tint = "{ 'bg-app-in/[0.07]': r.flow === 'in', 'bg-app-out/[0.06]': r.flow === 'out', 'bg-app-wait/[0.08]': r.flow === 'pending', 'bg-app-tile/60': r.flow === 'none' }";
    $ink = "{ 'text-app-in': r.flow === 'in', 'text-app-out': r.flow === 'out', 'text-app-wait': r.flow === 'pending', 'text-app-muted': r.flow === 'none' }";
    $bubble = "{ 'bg-app-in/15 text-app-in': r.flow === 'in', 'bg-app-out/10 text-app-out': r.flow === 'out', 'bg-app-wait/15 text-app-wait': r.flow === 'pending', 'bg-app-tile text-app-muted': r.flow === 'none' }";
@endphp
<li>
  <a :href="r.url" @click.prevent="sharet.panel('transaction', { url: r.url })" class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition hover:brightness-95" :class="{{ $tint }}">
    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full" :class="{{ $bubble }}">
      @include('pay.partials.icon', ['expr' => 'r', 'class' => 'h-[18px] w-[18px]'])
    </span>
    <span class="min-w-0 flex-1 truncate text-sm">
      <span class="font-semibold" x-text="r.title"></span>
      <span class="text-app-muted"> · <span x-text="r.from"></span> → <span x-text="r.to"></span></span>
    </span>
    <span class="row-meta app-num hidden shrink-0 text-xs text-app-muted lg:block" x-text="r.date"></span>
    <span class="row-meta hidden w-28 shrink-0 truncate text-right text-xs font-medium sm:block" :class="{{ $ink }}" x-text="r.state.label"></span>
    <span class="w-24 shrink-0 text-right text-[15px] font-bold tabular-nums" :class="{ ...{{ $ink }}, 'line-through': r.flow === 'none' }" x-text="r.signed"></span>
  </a>
</li>
