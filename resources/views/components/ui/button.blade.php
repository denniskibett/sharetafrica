{{-- Buttons. variant: primary (black on light / lime on dark, readable both ways) | secondary | danger. --}}
@props(['variant' => 'primary', 'icon' => null, 'href' => null])
@php
    $base = 'inline-flex h-10 items-center justify-center gap-2 rounded-lg px-4 text-sm font-medium transition disabled:cursor-not-allowed disabled:opacity-50';
    $look = [
        'primary' => 'bg-gray-900 text-white hover:bg-gray-800 dark:bg-[#D4FF3D] dark:text-gray-900 dark:hover:bg-[#c4ef2d]',
        'secondary' => 'border border-gray-300 bg-white text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/[0.06]',
        'danger' => 'bg-red-600 text-white hover:bg-red-700',
    ][$variant] ?? '';
    $tag = $href || $attributes->has('x-bind:href') ? 'a' : 'button';
@endphp
<{{ $tag }} @if ($href) href="{{ $href }}" @elseif ($tag === 'button') type="{{ $attributes->get('type', 'button') }}" @endif {{ $attributes->except('type')->merge(['class' => "$base $look"]) }}>
  @if ($icon)<x-dynamic-component :component="'heroicon-o-'.$icon" class="h-5 w-5" />@endif
  {{ $slot }}
</{{ $tag }}>
