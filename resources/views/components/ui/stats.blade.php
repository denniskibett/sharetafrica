{{-- One row of stat cards (CRM style). Children are <x-ui.stat>; :cols = how many sit side by side on wide screens. --}}
@props(['cols' => 4])
@php
    $grid = [2 => 'lg:grid-cols-2', 3 => 'lg:grid-cols-3', 4 => 'lg:grid-cols-4', 5 => 'lg:grid-cols-5', 6 => 'lg:grid-cols-3 2xl:grid-cols-6'][$cols] ?? 'lg:grid-cols-4';
@endphp
<div {{ $attributes->merge(['class' => "grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-6 $grid"]) }}>
  {{ $slot }}
</div>
