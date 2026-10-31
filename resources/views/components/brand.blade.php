{{-- Sharet Africa logo from the brand kit (public/images/brand): the colour logo, the lime two-lane tile
     with "sharet africa" in black (white in dark mode). `mark-only` shows just the tile (collapsed sidebar). --}}
@props(['size' => 32, 'markOnly' => false])
<span {{ $attributes->merge(['class' => 'inline-flex items-center']) }}>
    @if ($markOnly)
        <img src="{{ asset('images/brand/sharet-app-icon.svg') }}" alt="Sharet Africa" style="height: {{ $size }}px" class="w-auto">
    @else
        <img src="{{ asset('images/brand/sharet-logo-colour-on-light.svg') }}" alt="Sharet Africa" style="height: {{ round($size * 1.25) }}px" class="w-auto dark:hidden">
        <img src="{{ asset('images/brand/sharet-logo-colour-on-dark.svg') }}" alt="Sharet Africa" style="height: {{ round($size * 1.25) }}px" class="hidden w-auto dark:block">
    @endif
</span>
