{{-- The standard icon for a transaction kind (RampOrder::present()['icon']), for Alpine lists: `$expr` names the row. --}}
@foreach (['arrow-down-left', 'arrow-down-tray', 'at-symbol', 'building-office', 'shopping-bag', 'building-library', 'banknotes', 'device-phone-mobile'] as $name)
  <x-dynamic-component :component="'heroicon-o-'.$name" x-show="{{ $expr }}?.icon === '{{ $name }}'" class="{{ $class ?? 'h-5 w-5' }}" />
@endforeach
