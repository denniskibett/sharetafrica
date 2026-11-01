{{-- Customer app top bar, the same on every screen: logo (home), "Hi, V2", light/dark, and the avatar. Phones: the
     avatar opens the Account sheet. Wider screens add notifications (latest payments) and the account menu
     (transactions, website, profile, settings). --}}
@php
    $user = auth()->user();
    $initials = \App\Support\Table::person($user)['initials'];
    $first = \Illuminate\Support\Str::before(trim($user->name), ' ') ?: $user->name;
    $icon = 'flex h-11 w-11 items-center justify-center rounded-xl bg-app-tile hover:bg-app-raised';
@endphp
<header class="sticky top-0 z-[999] border-b border-app-line bg-app-bg/90 backdrop-blur">
 <div class="flex h-[72px] w-full items-center gap-3 px-4 md:px-6 2xl:px-8">
  <a href="{{ route('dashboard') }}" class="shrink-0" aria-label="Sharet home">
    <img src="{{ asset('images/brand/sharet-logo-colour-on-dark.svg') }}" alt="Sharet" class="hidden h-6 w-auto dark:block md:h-7">
    <img src="{{ asset('images/brand/sharet-logo-colour-on-light.svg') }}" alt="Sharet" class="h-6 w-auto dark:hidden md:h-7">
  </a>
  <p class="min-w-0 truncate border-l border-app-line pl-3 text-sm font-semibold md:ml-2 md:pl-5 md:text-base">Hi, {{ $first }}</p>

  <div class="ml-auto flex shrink-0 items-center gap-2 md:gap-3">
    <button type="button" @click="darkMode = !darkMode" class="{{ $icon }}" :aria-label="darkMode ? 'Light mode' : 'Dark mode'">
      <x-heroicon-o-sun x-show="darkMode" class="h-5 w-5" /><x-heroicon-o-moon x-show="!darkMode" class="h-5 w-5" />
    </button>
    {{-- Alerts: the latest payments (a dot while one is still moving); a row opens its receipt --}}
    <div class="relative hidden md:block" x-data="{ open: false, rows: [], async load() { this.rows = (await (await fetch('{{ route('pay.index') }}?per_page=5&sort=-date', { headers: { Accept: 'application/json' } })).json()).data } }"
         x-init="load()" @wallet-changed.window="load()" @click.outside="open = false">
      <button type="button" @click="open = !open; open && load()" aria-label="Notifications" class="{{ $icon }} relative">
        <x-heroicon-o-bell class="h-5 w-5" />
        <span x-show="rows.some(r => r.flow === 'pending')" x-cloak class="absolute right-2.5 top-2.5 h-2 w-2 rounded-full bg-app-wait"></span>
      </button>
      <div x-show="open" x-cloak x-transition class="absolute right-0 mt-3 w-[min(360px,calc(100vw-2rem))] rounded-2xl border border-app-line bg-app-panel p-3 shadow-xl">
        <p class="px-2 pb-1 text-sm font-semibold">Latest payments</p>
        <ul x-show="rows.length" class="space-y-1 [&_.row-meta]:!hidden" @click="open = false">
          <template x-for="r in rows" :key="r.id">@include('pay.partials.row')</template>
        </ul>
        <p x-show="!rows.length" class="px-2 py-6 text-center text-sm text-app-muted">Payment alerts will show here.</p>
        <a href="{{ route('pay.index') }}" class="mt-1 block rounded-lg px-2 py-2 text-center text-sm font-medium text-app-soft hover:bg-app-tile">See all transactions</a>
      </div>
    </div>
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

