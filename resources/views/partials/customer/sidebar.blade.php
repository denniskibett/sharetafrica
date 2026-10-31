{{-- Customer app sidebar (desktop and tablet). Brand look from the canvas "Web dashboard": near-black panel,
     lime-tinted current item. Same menu as everywhere (App\Support\DashboardNav); unbuilt pages sit under "Coming soon". --}}
@php $nav = \App\Support\DashboardNav::for(auth()->user()); @endphp
<aside class="sticky top-0 hidden h-screen w-[260px] shrink-0 flex-col border-r border-white/[0.08] bg-[#111114] px-4 py-6 md:flex">
  <a href="{{ route('dashboard') }}" class="mb-7 px-2" aria-label="Sharet home">
    <img src="{{ asset('images/brand/sharet-logo-compact-on-dark.svg') }}" alt="Sharet" class="h-7 w-auto">
  </a>
  <nav class="no-scrollbar flex-1 overflow-y-auto" aria-label="Main">
    @foreach ($nav as $group)
      @if (! empty($group['soon']))
        <div x-data="{ open: false }" class="mt-6 border-t border-white/[0.08] pt-4">
          <button type="button" @click="open = !open" class="app-mono flex w-full items-center justify-between px-3 text-[11px] uppercase tracking-[0.16em] text-[#80807A] hover:text-[#C9C9C2]">
            Coming soon ({{ count($group['items']) }}) <x-heroicon-o-chevron-down class="h-4 w-4 transition" x-bind:class="open && 'rotate-180'" />
          </button>
          <ul x-show="open" x-collapse x-cloak class="mt-2 flex flex-col gap-1">
            @foreach ($group['items'] as $item)
              <li class="flex items-center gap-3 rounded-[10px] px-3 py-2.5 text-[15px] text-[#5A5A55]">
                <x-dynamic-component :component="'heroicon-o-'.$item['icon']" class="h-5 w-5" /> {{ $item['label'] }}
              </li>
            @endforeach
          </ul>
        </div>
      @else
        @if (! $loop->first)<p class="app-mono mb-2 mt-6 px-3 text-[11px] uppercase tracking-[0.16em] text-[#80807A]">{{ $group['title'] }}</p>@endif
        <ul class="flex flex-col gap-1">
          @foreach ($group['items'] as $item)
            <li>
              <a href="{{ $item['url'] }}" @if ($item['current']) aria-current="page" @endif
                 @class(['flex items-center gap-3 rounded-[10px] px-3 py-3 text-[15px] transition',
                   'bg-[#D4FF3D]/[0.12] text-[#D4FF3D]' => $item['current'],
                   'text-[#C9C9C2] hover:bg-white/[0.04] hover:text-[#F4F4F0]' => ! $item['current']])>
                <x-dynamic-component :component="'heroicon-o-'.$item['icon']" class="h-5 w-5 shrink-0" /> {{ $item['label'] }}
              </a>
            </li>
          @endforeach
        </ul>
      @endif
    @endforeach
  </nav>
  @php $user = auth()->user(); @endphp
  <div class="mt-4 rounded-[14px] bg-[#18181C] p-4">
    <p class="truncate text-sm font-semibold">{{ $user->company?->name ?? $user->name }}</p>
    <p class="app-mono mt-1 text-[11px] uppercase tracking-[0.14em] text-[#80807A]">{{ ['standard' => 'Bank-grade verified', 'basic' => 'Basic account', 'none' => 'Not verified'][$user->kyc_level ?? 'none'] ?? 'Account' }}</p>
  </div>
</aside>
