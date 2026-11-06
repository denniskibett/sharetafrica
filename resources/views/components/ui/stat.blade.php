{{-- Stat card. value/delta/hint are Alpine expressions read from the page's JSON (e.g. value="stats.total").
     format: number | money | raw. delta is a % change vs last period; positive is green, negative red. --}}
@props(['label', 'value', 'format' => 'number', 'delta' => null, 'hint' => null, 'icon' => null, 'tone' => 'violet'])
@php
    $tones = [
        // Sharet UI colours by meaning: pay violet, money in green, send blue, trade amber, danger red, abroad teal, Sharet lime
        'violet' => 'bg-app-pay/15 text-app-pay',
        'emerald' => 'bg-app-in/15 text-app-in',
        'sky' => 'bg-app-send/15 text-app-send',
        'amber' => 'bg-app-trade/15 text-app-trade',
        'rose' => 'bg-app-out/15 text-app-out',
        'indigo' => 'bg-app-send/15 text-app-send',
        'teal' => 'bg-app-abroad/15 text-app-abroad',
        'lime' => 'bg-app-lime text-[#050507]',
    ];
    $text = match ($format) { 'money' => "sharet.money($value)", 'raw' => "($value) ?? '—'", default => "sharet.number($value)" };
@endphp
<div {{ $attributes->merge(['class' => 'ui-card rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6']) }}>
  <div class="flex items-start justify-between gap-3">
    <h4 @class(['font-bold text-gray-800 dark:text-white/90', 'text-2xl md:text-[28px] md:leading-9' => $format !== 'raw', 'text-lg leading-7 md:text-xl' => $format === 'raw']) x-text="{{ $text }}">—</h4>
    @if ($icon)
      <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full {{ $tones[$tone] ?? $tones['violet'] }}">
        <x-dynamic-component :component="'heroicon-o-'.$icon" class="h-5 w-5" />
      </span>
    @endif
  </div>
  <div class="mt-4 flex items-end justify-between gap-2 sm:mt-5">
    <p class="text-sm text-gray-700 dark:text-gray-400">{{ $label }}</p>
    <div class="flex items-center gap-1.5">
      @if ($delta)
        <span x-show="({{ $delta }}) !== null && ({{ $delta }}) !== undefined"
              :class="({{ $delta }}) >= 0 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400' : 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-400'"
              class="rounded-full px-2 py-0.5 text-xs font-medium"
              x-text="(({{ $delta }}) >= 0 ? '+' : '') + ({{ $delta }}) + '%'"></span>
      @endif
      @if ($hint)
        <span class="text-xs text-gray-500 dark:text-gray-400" x-text="{{ $hint }}"></span>
      @endif
    </div>
  </div>
</div>
