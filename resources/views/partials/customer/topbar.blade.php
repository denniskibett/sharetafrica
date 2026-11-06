{{-- Customer app top bar, the same on every screen.
     Phones: Sharet logo left; photo and notifications right; "Habari, Juma" with the country and today's rates
     (Safaricom's rate less our margin) on the line below.
     Wider screens: logo, notifications, greeting and rates in one row; light/dark and the account menu right. --}}
@php
    $user = auth()->user();
    $initials = \App\Support\Table::person($user)['initials'];
    $first = \Illuminate\Support\Str::before(trim($user->name), ' ') ?: $user->name;
    $icon = 'flex h-11 w-11 items-center justify-center rounded-full bg-app-tile hover:bg-app-raised';
    [$cc, $countryName] = \App\Support\HomeRates::country($user);
    $rates = \App\Support\HomeRates::line();
    $flags = ['KE' => 'linear-gradient(#0A0A0C 0 30%,#fff 30% 35%,#B00 35% 65%,#fff 65% 70%,#076B32 70%)', 'UG' => 'linear-gradient(#0A0A0C 0 17%,#FCDC04 17% 33%,#D90000 33% 50%,#0A0A0C 50% 67%,#FCDC04 67% 83%,#D90000 83%)',
              'TZ' => 'linear-gradient(135deg,#1EB53A 0 40%,#FCD116 40% 44%,#0A0A0C 44% 56%,#FCD116 56% 60%,#00A3DD 60%)', 'RW' => 'linear-gradient(#20603D 0 50%,#FAD201 50% 75%,#00A1DE 75%)'];
@endphp
<header class="sticky top-0 z-[999] border-b border-app-line bg-app-bg/90 backdrop-blur">
 <div class="flex w-full flex-wrap items-center gap-x-3 gap-y-2 px-4 py-3 md:h-[72px] md:flex-nowrap md:px-6 md:py-0 2xl:px-8">
  <a href="{{ route('dashboard') }}" class="order-1 shrink-0 md:pr-2" aria-label="Sharet home">
    <img src="{{ asset('images/brand/sharet-logo-colour-on-dark.svg') }}" alt="Sharet" class="hidden h-6 w-auto dark:block md:h-7">
    <img src="{{ asset('images/brand/sharet-logo-colour-on-light.svg') }}" alt="Sharet" class="h-6 w-auto dark:hidden md:h-7">
  </a>
    {{-- Alerts: the latest payments (a dot while one is still moving); a row opens its receipt --}}
    <div class="relative order-3 shrink-0 md:order-2" x-data="{ open: false, rows: [], async load() { this.rows = (await (await fetch('{{ route('pay.index') }}?per_page=5&sort=-date', { headers: { Accept: 'application/json' } })).json()).data } }"
         x-init="load()" @wallet-changed.window="load()" @click.outside="open = false">
      <button type="button" @click="open = !open; open && load()" aria-label="Notifications" class="{{ $icon }} relative">
        <x-heroicon-o-bell class="h-5 w-5" />
        <span x-show="rows.some(r => r.flow === 'pending')" x-cloak class="absolute right-2.5 top-2.5 h-2 w-2 rounded-full bg-app-wait ring-2 ring-app-tile"></span>
      </button>
      <div x-show="open" x-cloak x-transition class="absolute right-0 mt-3 w-[min(360px,calc(100vw-2rem))] md:left-0 md:right-auto rounded-2xl border border-app-line bg-app-panel p-3 shadow-xl">
        <p class="px-2 pb-1 text-sm font-semibold">Latest payments</p>
        <ul x-show="rows.length" class="space-y-1 [&_.row-meta]:!hidden" @click="open = false">
          <template x-for="r in rows" :key="r.id">@include('pay.partials.row')</template>
        </ul>
        <p x-show="!rows.length" class="px-2 py-6 text-center text-sm text-app-muted">Payment alerts will show here.</p>
        <a href="{{ route('pay.index') }}" class="mt-1 block rounded-lg px-2 py-2 text-center text-sm font-medium text-app-soft hover:bg-app-tile">See all transactions</a>
      </div>
    </div>
  <div class="order-4 w-full min-w-0 leading-tight md:order-3 md:w-auto">
    <p class="truncate text-[15px] font-semibold md:text-base">Habari, {{ $first }}</p>
    <p class="mt-0.5 flex items-center gap-1.5 truncate text-xs text-app-muted">
      <span class="inline-block h-[11px] w-4 shrink-0 rounded-[2px]" style="background: {{ $flags[$cc] }}" aria-hidden="true"></span>
      <span class="truncate"><span class="hidden sm:inline">{{ $countryName }} · </span><span class="sr-only sm:hidden">{{ $countryName }}: </span>{{ $rates ?? "Today's rates from 9:00" }}</span>
    </p>
  </div>

  <div class="order-2 ml-auto flex shrink-0 items-center gap-2 md:order-4 md:gap-3">
    <button type="button" @click="darkMode = !darkMode" class="{{ $icon }} !hidden md:!flex" :aria-label="darkMode ? 'Light mode' : 'Dark mode'">
      <x-heroicon-o-sun x-show="darkMode" class="h-5 w-5" /><x-heroicon-o-moon x-show="!darkMode" class="h-5 w-5" />
    </button>
    <button type="button" @click="$dispatch('account-sheet')" class="flex h-11 w-11 items-center justify-center overflow-hidden rounded-full bg-[#050507] text-[#D4FF3D] dark:bg-[#D4FF3D] dark:text-[#050507] text-sm font-semibold md:hidden" aria-label="Account">
      @if ($user->avatar)<img src="{{ asset('storage/'.$user->avatar) }}" alt="" class="h-full w-full object-cover">@else{{ $initials }}@endif
    </button>
    <div class="relative hidden md:block" x-data="{ open: false }" @click.outside="open = false">
      <button type="button" @click="open = !open" class="flex items-center gap-2 rounded-full py-1 pl-1 pr-2 hover:bg-app-tile" aria-label="Account menu">
        <span class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full bg-[#050507] text-[#D4FF3D] dark:bg-[#D4FF3D] dark:text-[#050507] text-sm font-semibold">
          @if ($user->avatar)<img src="{{ asset('storage/'.$user->avatar) }}" alt="" class="h-full w-full object-cover">@else{{ $initials }}@endif
        </span>
        <x-heroicon-o-chevron-down class="h-4 w-4 text-app-muted" />
      </button>
      <div x-show="open" x-cloak x-transition class="absolute right-0 mt-3 w-64 rounded-2xl border border-app-line bg-app-panel p-2 shadow-xl">
        <p class="truncate px-3 pb-2 pt-1 text-sm font-semibold">{{ $user->name }}<span class="block truncate text-xs font-normal text-app-muted">{{ $user->email }}</span></p>
        @include('partials.customer.account-links')
      </div>
    </div>
  </div>
 </div>
</header>

