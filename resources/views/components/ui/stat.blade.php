{{-- Stat card. value/delta/hint are Alpine expressions read from the page's JSON (e.g. value="stats.total").
     format: number | money | raw. delta is a % change vs last period; positive is green, negative red. --}}
@props(['label', 'value', 'format' => 'number', 'delta' => null, 'hint' => null, 'icon' => null, 'tone' => 'violet'])
@php
    $tones = [
        'violet' => 'bg-violet-100 text-violet-600 dark:bg-violet-500/15 dark:text-violet-300',
        'emerald' => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300',
        'sky' => 'bg-sky-100 text-sky-600 dark:bg-sky-500/15 dark:text-sky-300',
        'amber' => 'bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300',
        'rose' => 'bg-rose-100 text-rose-600 dark:bg-rose-500/15 dark:text-rose-300',
        'indigo' => 'bg-indigo-100 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300',
        'teal' => 'bg-teal-100 text-teal-600 dark:bg-teal-500/15 dark:text-teal-300',
        'lime' => 'bg-gray-900 text-[#D4FF3D] dark:bg-[#D4FF3D]/15 dark:text-[#D4FF3D]',
    ];
    $text = match ($format) { 'money' => "sharet.money($value)", 'raw' => "($value) ?? '—'", default => "sharet.number($value)" };
@endphp
<div {{ $attributes->merge(['class' => 'rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] md:p-6']) }}>
  <div class="flex items-start justify-between gap-3">
    <h4 class="text-2xl font-bold text-gray-800 dark:text-white/90 md:text-[28px] md:leading-9" x-text="{{ $text }}">—</h4>
    @if ($icon)
      <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $tones[$tone] ?? $tones['violet'] }}">
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
