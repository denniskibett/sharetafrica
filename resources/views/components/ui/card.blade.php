{{-- Panel with a title row. Slots: actions (top right), default body. --}}
@props(['title' => null, 'subtitle' => null, 'padded' => true])
<div {{ $attributes->merge(['class' => 'ui-card rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]']) }}>
  @if ($title || isset($actions))
    <div class="flex flex-col gap-3 px-5 pt-5 sm:flex-row sm:items-center sm:justify-between sm:px-6 sm:pt-6">
      <div>
        @if ($title)<h3 class="text-[17px] font-semibold tracking-tight text-gray-800 dark:text-white/90">{{ $title }}</h3>@endif
        @if ($subtitle)<p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $subtitle }}</p>@endif
      </div>
      @isset($actions)<div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>@endisset
    </div>
  @endif
  <div @class(['p-5 sm:p-6' => $padded])>{{ $slot }}</div>
</div>
