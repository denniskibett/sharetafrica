{{-- Sharet Africa logo from the brand kit (public/images/brand). Light/dark versions swap with the theme.
     `mark-only` shows just the symbol (collapsed sidebar, small spaces). --}}
@props(['size' => 32, 'markOnly' => false])
<span {{ $attributes->merge(['class' => 'inline-flex items-center']) }}>
    @if ($markOnly)
        <img src="{{ asset('images/brand/sharet-symbol-on-light.svg') }}" alt="Sharet Africa" style="height: {{ $size }}px" class="w-auto dark:hidden">
        <img src="{{ asset('images/brand/sharet-symbol-on-dark.svg') }}" alt="Sharet Africa" style="height: {{ $size }}px" class="hidden w-auto dark:block">
    @else
        <img src="{{ asset('images/brand/sharet-logo-on-light.svg') }}" alt="Sharet Africa" style="height: {{ round($size * 1.25) }}px" class="w-auto dark:hidden">
        <img src="{{ asset('images/brand/sharet-logo-on-dark.svg') }}" alt="Sharet Africa" style="height: {{ round($size * 1.25) }}px" class="hidden w-auto dark:block">
    @endif
</span>
