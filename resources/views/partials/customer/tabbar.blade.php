{{-- Phone tab bar (like the canvas mobile app): Home, Wallet, the lane's main action, Profile, More.
     "More" opens a sheet with the full menu. Hidden from tablet width up (the sidebar takes over). --}}
@php
    $user = auth()->user();
    [$laneLabel, $laneRoute, $laneIcon] = \App\Support\DashboardNav::laneAction($user);
    $tabs = [
        ['Home', 'dashboard', 'home', ['dashboard']],
        ['Wallet', 'wallet.index', 'wallet', ['wallet.*']],
        [$laneLabel, $laneRoute, $laneIcon, [$laneRoute]],
        ['Profile', 'profile.show', 'user-circle', ['profile.*']],
    ];
    $nav = \App\Support\DashboardNav::for($user);
@endphp
<div x-data="{ more: false }" class="md:hidden">
  <nav class="fixed inset-x-0 bottom-0 z-[1000] flex items-center justify-around border-t border-white/[0.08] bg-[#111114]/95 px-2 pb-[max(env(safe-area-inset-bottom),8px)] pt-2 backdrop-blur" aria-label="App">
    @foreach ($tabs as [$label, $route, $icon, $active])
      @php $ready = Route::has($route); $current = $ready && request()->routeIs(...$active); @endphp
      <a @if ($ready) href="{{ route($route) }}" @else aria-disabled="true" @endif @if ($current) aria-current="page" @endif
         @class(['flex h-[52px] w-16 flex-col items-center justify-center gap-1 text-[11px]', 'text-[#D4FF3D]' => $current, 'text-[#80807A]' => ! $current && $ready, 'text-[#3A3A3F]' => ! $ready])>
        <x-dynamic-component :component="'heroicon-o-'.$icon" class="h-6 w-6" /> {{ $label }}
      </a>
    @endforeach
    <button type="button" @click="more = true" class="flex h-[52px] w-16 flex-col items-center justify-center gap-1 text-[11px] text-[#80807A]" :class="more && 'text-[#D4FF3D]'">
      <x-heroicon-o-squares-2x2 class="h-6 w-6" /> More
    </button>
  </nav>

  {{-- More sheet --}}
  <div x-show="more" x-cloak class="fixed inset-0 z-[1001]" @keydown.escape.window="more = false">
    <div x-show="more" x-transition.opacity class="absolute inset-0 bg-black/60" @click="more = false"></div>
    <div x-show="more" x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
         x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full"
         class="absolute inset-x-0 bottom-0 max-h-[85vh] overflow-y-auto rounded-t-3xl bg-[#111114] px-4 pb-[max(env(safe-area-inset-bottom),20px)] pt-3">
      <div class="mx-auto mb-4 h-1.5 w-10 rounded-full bg-white/20"></div>
      @foreach ($nav as $group)
        <p class="app-mono mb-2 mt-4 px-2 text-[11px] uppercase tracking-[0.16em] text-[#80807A]">{{ $group['title'] }}</p>
        <div class="grid grid-cols-4 gap-2">
          @foreach ($group['items'] as $item)
            <a @if ($item['ready']) href="{{ $item['url'] }}" @endif class="flex flex-col items-center gap-2 rounded-2xl p-2 text-center text-[12px] {{ $item['ready'] ? 'text-[#F4F4F0]' : 'text-[#5A5A55]' }}">
              <span class="flex h-14 w-14 items-center justify-center rounded-2xl {{ $item['current'] ? 'bg-[#D4FF3D] text-[#050507]' : 'bg-[#18181C]' }}"><x-dynamic-component :component="'heroicon-o-'.$item['icon']" class="h-6 w-6" /></span>
              <span class="leading-tight">{{ $item['label'] }}</span>
            </a>
          @endforeach
        </div>
      @endforeach
      <div class="mt-6 rounded-2xl bg-[#18181C] p-2">@include('partials.customer.account-links')</div>
    </div>
  </div>
</div>
