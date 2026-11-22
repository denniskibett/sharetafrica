{{-- Customer sidebar (tablet width up), as on the canvas board "Dashboard · Customer web": an ink column with the
     sharet. wordmark, the current page in lime, small mono section labels. Phones keep the tab bar and Account sheet. --}}
@php
    $user = auth()->user();
    $groups = [
        ['', [
            ['Home', route('dashboard'), 'home', request()->routeIs('dashboard')],
            ['Transactions', route('pay.index'), 'arrows-right-left', request()->routeIs('pay.*')],
            ['Request and split', route('requests'), 'arrow-down-left', request()->routeIs('requests*')],
            ['Send abroad', route('abroad'), 'globe-europe-africa', request()->routeIs('abroad'), 'Soon'],
            ['Card', route('card'), 'credit-card', request()->routeIs('card'), 'Soon'],
        ]],
        ['BUSINESS', array_values(array_filter([
            [$user->company_id ? 'Business settings' : 'Start a business', route('settings.company'), 'building-storefront', request()->routeIs('settings.company*')],
            $user->canAny(['developer.api_keys', 'merchant.accept']) ? ['API keys', route('api-keys.index'), 'key', request()->routeIs('api-keys.*')] : null,
            $user->canAny(['developer.api_keys', 'merchant.accept']) ? ['Developer docs', route('docs'), 'code-bracket', false] : null,
        ]))],
        ['ACCOUNT', [
            ['Profile', route('profile.show'), 'user-circle', request()->routeIs('profile.show')],
            ['Settings', route('profile.edit'), 'cog-6-tooth', request()->routeIs('profile.edit')],
            ['Connected apps', route('connections.index'), 'link', request()->routeIs('connections.*')],
            ['Help', route('help'), 'lifebuoy', request()->routeIs('help')],
        ]],
    ];
@endphp
<aside class="sh-side hidden md:flex" aria-label="Menu">
  <a href="{{ route('dashboard') }}" class="sh-lg" aria-label="Sharet home">sharet<i>.</i></a>
  @foreach ($groups as [$title, $items])
    @if ($title)<small>{{ $title }}</small>@endif
    @foreach ($items as $item)
      @php [$label, $url, $icon, $current] = $item; $soon = $item[4] ?? null; @endphp
      <a href="{{ $url }}" @class(['on' => $current]) @if ($current) aria-current="page" @endif>
        <x-dynamic-component :component="'heroicon-o-'.$icon" class="h-[18px] w-[18px] shrink-0" />
        <span class="truncate">{{ $label }}</span>
        @if ($soon)<em>{{ $soon }}</em>@endif
      </a>
    @endforeach
  @endforeach
  <div class="mt-auto pt-4">
    <button type="button" @click="darkMode = !darkMode" class="sh-sbtn">
      <x-heroicon-o-sun x-show="darkMode" class="h-[18px] w-[18px]" /><x-heroicon-o-moon x-show="!darkMode" class="h-[18px] w-[18px]" />
      <span x-text="darkMode ? 'Light mode' : 'Dark mode'"></span>
    </button>
    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="sh-sbtn"><x-heroicon-o-arrow-left-start-on-rectangle class="h-[18px] w-[18px]" /> Sign out</button>
    </form>
  </div>
</aside>
