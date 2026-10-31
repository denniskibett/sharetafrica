{{-- One transaction in a list, as in the mobile app (needs Alpine `r`, RampOrder::present()). Opens the receipt panel.
     Colours: money in green, money out red, money that never moved struck through; the status in its own colour. --}}
@php
    $expr = 'r';
    $amount = "{ 'text-app-in': r.flow === 'in', 'text-app-out': r.flow === 'out', 'text-app-muted line-through': r.flow === 'none', 'text-app-wait': r.flow === 'pending' }";
    $bubble = "{ 'bg-app-in/15 text-app-in': r.flow === 'in', 'bg-app-out/10 text-app-out': r.flow === 'out', 'bg-app-tile text-app-muted': r.flow === 'none', 'bg-app-wait/15 text-app-wait': r.flow === 'pending' }";
    $tone = "{ 'text-app-in': r.state.tone === 'success', 'text-app-wait': r.state.tone === 'warning', 'text-app-info': r.state.tone === 'info', 'text-app-out': r.state.tone === 'error', 'text-app-muted': r.state.tone === 'neutral' }";
@endphp
<li>
  <a :href="r.url" @click.prevent="sharet.panel('transaction', { url: r.url })" class="flex items-center gap-3 border-b border-app-line py-3 last:border-0 hover:opacity-80">
    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full" :class="{{ $bubble }}">
      @include('pay.partials.icon', ['expr' => 'r'])
    </span>
    <span class="min-w-0 flex-1">
      <span class="block truncate text-[15px] font-medium" x-text="r.title"></span>
      <span class="block truncate text-xs"><span class="app-num text-app-muted" x-text="r.date"></span> · <span class="font-medium" :class="{{ $tone }}" x-text="r.state.label"></span></span>
    </span>
    <span class="shrink-0 text-sm tabular-nums" :class="{{ $amount }}" x-text="r.signed"></span>
  </a>
</li>
