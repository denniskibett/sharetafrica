{{-- Buttons (Sharet UI, pill-shaped). variant: primary (ink on light / lime on dark) | secondary (filled grey) | danger. --}}
@props(['variant' => 'primary', 'icon' => null, 'href' => null])
@php
    $base = 'inline-flex h-11 items-center justify-center gap-2 rounded-full px-5 text-sm font-semibold transition disabled:cursor-not-allowed disabled:opacity-50';
    $look = [
        'primary' => 'bg-gray-900 text-white hover:bg-gray-800 dark:bg-[#D4FF3D] dark:text-gray-900 dark:hover:bg-[#c4ef2d]',
        'secondary' => 'bg-gray-100 text-gray-800 hover:bg-gray-200 dark:bg-white/[0.06] dark:text-gray-200 dark:hover:bg-white/10',
        'danger' => 'bg-red-600 text-white hover:bg-red-700',
    ][$variant] ?? '';
    $tag = $href || $attributes->has('x-bind:href') ? 'a' : 'button';
@endphp
<{{ $tag }} @if ($href) href="{{ $href }}" @elseif ($tag === 'button') type="{{ $attributes->get('type', 'button') }}" @endif {{ $attributes->except('type')->merge(['class' => "$base $look"]) }}>
  @if ($icon)<x-dynamic-component :component="'heroicon-o-'.$icon" class="h-5 w-5" />@endif
  {{ $slot }}
</{{ $tag }}>
