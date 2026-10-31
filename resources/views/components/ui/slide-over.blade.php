{{-- Right-hand slide-over panel over a 50% black backdrop. Open with sharet.panel('{name}', payload);
     inside, `payload` holds what was passed. Slots: default body, footer. Esc or the backdrop closes it. --}}
@props(['name', 'title' => '', 'subtitle' => null, 'width' => 'max-w-xl'])
<div x-data="{ open: false, payload: {}, close() { this.open = false } }"
     @slide-over.window="if ($event.detail.name === '{{ $name }}') { payload = $event.detail.payload || {}; open = true }"
     @slide-over-close.window="open = false"
     @keydown.escape.window="open = false"
     x-show="open" x-cloak class="fixed inset-0 z-[100001]" role="dialog" aria-modal="true" aria-label="{{ $title }}">
  <div x-show="open" x-transition.opacity class="absolute inset-0 bg-black/50" @click="close()"></div>
  <div x-show="open" x-trap.noscroll="open"
       x-transition:enter="transform transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
       x-transition:leave="transform transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
       class="absolute inset-y-0 right-0 flex w-full {{ $width }} flex-col bg-white shadow-xl dark:bg-gray-900">
    <div class="flex items-start justify-between gap-4 border-b border-gray-200 px-6 py-5 dark:border-gray-800">
      <div>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white" {{ $attributes->only('x-text') }}>{{ $title }}</h2>
        @if ($subtitle)<p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $subtitle }}</p>@endif
      </div>
      <button type="button" @click="close()" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/5 dark:hover:text-white" aria-label="Close"><x-heroicon-o-x-mark class="h-6 w-6" /></button>
    </div>
    <div class="flex-1 overflow-y-auto px-6 py-5">{{ $slot }}</div>
    @isset($footer)<div class="flex flex-wrap justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-gray-800">{{ $footer }}</div>@endisset
  </div>
</div>
