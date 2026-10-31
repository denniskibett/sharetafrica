{{-- One entry in <x-ui.row-menu>. href makes it a link; otherwise pass @click. danger = red. --}}
@props(['icon' => null, 'href' => null, 'danger' => false])
@php $tag = $href || $attributes->has('x-bind:href') || $attributes->has(':href') ? 'a' : 'button'; @endphp
<{{ $tag }} role="menuitem" @if ($href) href="{{ $href }}" @elseif ($tag === 'button') type="button" @endif
  {{ $attributes->class(['flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium',
      'text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10' => $danger,
      'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5' => ! $danger]) }}>
  @if ($icon)<x-dynamic-component :component="'heroicon-o-'.$icon" class="h-4 w-4 shrink-0" />@endif
  {{ $slot }}
</{{ $tag }}>
