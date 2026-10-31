{{-- ApexCharts panel. The controller URL returns ApexCharts options as JSON ({ type, series, xaxis... }).
     ranges: optional day ranges shown as a segmented control, sent as ?days=.
     sync: Alpine object expression from the page merged into the query, e.g. sync="{ lane: tab }" (redraws when it changes). --}}
@props(['url', 'title' => null, 'subtitle' => null, 'height' => 300, 'ranges' => null, 'days' => 30, 'sync' => null])
<div {{ $attributes->merge(['class' => 'rounded-2xl border border-gray-200 bg-white px-5 pb-5 pt-5 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 sm:pt-6']) }}
     x-data="chart({ url: '{{ $url }}', height: {{ (int) $height }}, params: {{ $ranges ? '{ days: '.(int) $days.' }' : '{}' }} })"
     @if ($sync) x-effect="Object.assign(params, {{ $sync }})" @endif>
  <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
      @if ($title)<h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $title }}</h3>@endif
      @if ($subtitle)<p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $subtitle }}</p>@endif
    </div>
    @if ($ranges)
      <div class="inline-flex items-center gap-0.5 rounded-lg bg-gray-100 p-0.5 dark:bg-gray-900">
        @foreach ($ranges as $value => $label)
          <button type="button" @click="params.days = {{ (int) $value }}"
                  :class="params.days === {{ (int) $value }} ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-gray-800 dark:text-white' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'"
                  class="rounded-md px-3 py-2 text-sm font-medium">{{ $label }}</button>
        @endforeach
      </div>
    @endif
    {{ $slot }}
  </div>
  <div x-ref="canvas" x-show="!empty" class="-ml-2 w-full" style="min-height: {{ (int) $height }}px"></div>
  <p x-show="loading" class="py-10 text-center text-sm text-gray-500 dark:text-gray-400">Loading chart…</p>
  <p x-show="!loading && empty" x-cloak class="flex items-center justify-center text-center text-sm text-gray-500 dark:text-gray-400" style="height: {{ (int) $height }}px">No data for this period yet.</p>
</div>
