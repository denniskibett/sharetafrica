{{-- Customer app top bar, tablet and desktop only (phones use the bottom tab bar alone): logo (home), greeting and
     time, theme, notifications (latest payments) and the account menu (transactions, website, profile, settings). --}}
@php
    $user = auth()->user();
    $hour = (int) now()->timezone('Africa/Nairobi')->format('G');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $initials = \App\Support\Table::person($user)['initials'];
    $first = \Illuminate\Support\Str::before(trim($user->name), ' ') ?: $user->name;
    $icon = 'flex h-11 w-11 items-center justify-center rounded-xl bg-app-tile hover:bg-app-raised';
@endphp
{{-- Phones have no top bar: the bottom tab bar is the only navigation there (Home, Activity, Send, Receive, Account) --}}
<header class="sticky top-0 z-[999] hidden border-b border-app-line bg-app-bg/90 backdrop-blur md:block">
 <div class="flex h-[72px] w-full items-center gap-3 px-4 md:px-6 2xl:px-8">
  <a href="{{ route('dashboard') }}" class="shrink-0" aria-label="Sharet home">
    <img src="{{ asset('images/brand/sharet-logo-colour-on-dark.svg') }}" alt="Sharet" class="hidden h-7 w-auto dark:block">
    <img src="{{ asset('images/brand/sharet-logo-colour-on-light.svg') }}" alt="Sharet" class="h-7 w-auto dark:hidden">
  </a>
  <p class="ml-5 hidden border-l border-app-line pl-5 lg:block" x-data="clock()">
    <span class="block text-sm font-semibold"><span x-text="greeting">{{ $greeting }}</span>, {{ $first }}</span>
    <span class="app-num block text-xs text-app-muted" x-text="time"></span>
  </p>

  <div class="ml-auto flex shrink-0 items-center gap-2 md:gap-3">
    <button type="button" @click="darkMode = !darkMode" class="{{ $icon }}" :aria-label="darkMode ? 'Light mode' : 'Dark mode'">
      <x-heroicon-o-sun x-show="darkMode" class="h-5 w-5" /><x-heroicon-o-moon x-show="!darkMode" class="h-5 w-5" />
    </button>
    {{-- Alerts: the latest payments (a dot while one is still moving); a row opens its receipt --}}
    <div class="relative" x-data="{ open: false, rows: [], async load() { this.rows = (await (await fetch('{{ route('pay.index') }}?per_page=5&sort=-date', { headers: { Accept: 'application/json' } })).json()).data } }"
         x-init="load()" @wallet-changed.window="load()" @click.outside="open = false">
      <button type="button" @click="open = !open; open && load()" aria-label="Notifications" class="{{ $icon }} relative">
        <x-heroicon-o-bell class="h-5 w-5" />
        <span x-show="rows.some(r => r.flow === 'pending')" x-cloak class="absolute right-2.5 top-2.5 h-2 w-2 rounded-full bg-app-wait"></span>
      </button>
      <div x-show="open" x-cloak x-transition class="absolute right-0 mt-3 w-[min(360px,calc(100vw-2rem))] rounded-2xl border border-app-line bg-app-panel p-3 shadow-xl">
        <p class="px-2 pb-1 text-sm font-semibold">Latest payments</p>
        <ul x-show="rows.length" class="px-2" @click="open = false">
          <template x-for="r in rows" :key="r.id">@include('pay.partials.row')</template>
        </ul>
        <p x-show="!rows.length" class="px-2 py-6 text-center text-sm text-app-muted">Payment alerts will show here.</p>
        <a href="{{ route('pay.index') }}" class="mt-1 block rounded-lg px-2 py-2 text-center text-sm font-medium text-app-soft hover:bg-app-tile">See all transactions</a>
      </div>
    </div>
    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
      <button type="button" @click="open = !open" class="flex items-center gap-2 rounded-full py-1 pl-1 pr-2 hover:bg-app-tile" aria-label="Account menu">
        <span class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full bg-app-raised text-sm font-semibold">
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

<script>
  /** Greeting and time on the viewer's own clock, updated every 20 seconds. */
  function clock() {
    const tick = (c) => {
      const d = new Date(), h = d.getHours();
      c.greeting = h < 12 ? 'Good morning' : h < 17 ? 'Good afternoon' : 'Good evening';
      c.time = d.toLocaleString('en-KE', { weekday: 'short', day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
    };
    return { greeting: @js($greeting), time: '', init() { tick(this); setInterval(() => tick(this), 20000); } };
  }
</script>
