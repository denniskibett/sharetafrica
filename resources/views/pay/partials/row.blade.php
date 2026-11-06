{{-- One transaction on one line, tinted in its colour (in green, out red, waiting orange, didn't move grey): icon,
     what it was with from → to, date, status and the signed amount. Needs Alpine `r` (RampOrder::present()); opens the
     receipt. Phones keep the same line and drop the date and status columns. --}}
@php
    $tint = "{ 'opacity-70': r.flow === 'none' }"; // Revolut-style: plain rows, the colour lives in the icon
    $ink = "{ 'text-app-in': r.flow === 'in', 'text-app-out': r.flow === 'out', 'text-app-wait': r.flow === 'pending', 'text-app-muted': r.flow === 'none' }";
    // Sharet UI: the icon bubble takes the colour of what was done (money in green, people blue, tills and bills violet,
    // banks orange, Sharet @names lime); pending stays orange and "didn't move" grey
    $bubble = "{ 'bg-app-in/15 text-app-in': r.flow === 'in', 'bg-app-send/15 text-app-send': r.flow === 'out' && r.icon === 'device-phone-mobile', 'bg-app-pay/15 text-app-pay': r.flow === 'out' && ['shopping-bag', 'building-office'].includes(r.icon), 'bg-orange-400/15 text-orange-500 dark:text-orange-300': r.flow === 'out' && ['building-library', 'banknotes'].includes(r.icon), 'bg-app-lime/20 text-app-accent': r.flow === 'out' && r.icon === 'at-symbol', 'bg-app-out/10 text-app-out': r.flow === 'out' && ! ['device-phone-mobile', 'shopping-bag', 'building-office', 'building-library', 'banknotes', 'at-symbol'].includes(r.icon), 'bg-app-wait/15 text-app-wait': r.flow === 'pending', 'bg-app-tile text-app-muted': r.flow === 'none' }";
@endphp
<li>
  <a :href="r.url" @click.prevent="sharet.panel('transaction', { url: r.url })" class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition hover:bg-app-tile" :class="{{ $tint }}">
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
