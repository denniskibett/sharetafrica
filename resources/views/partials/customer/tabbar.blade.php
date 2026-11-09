{{-- Phone tab bar (the canvas mobile app): Home, Activity, Send (centre), Receive, Account. Send and Receive open the
     dashboard's money panel (from other pages they go to the dashboard first).
     "Account" (or the avatar in the top bar) opens a sheet with the theme, transactions, website, profile, settings,
     verification and sign out. Hidden from tablet width up. --}}
@php
    $tabs = [
        ['Home', route('dashboard'), 'home', request()->routeIs('dashboard')],
        ['Activity', route('pay.index'), 'arrows-right-left', request()->routeIs('pay.*')],
        ['Receive', route('dashboard', ['panel' => 'receive']), 'arrow-down-left', false],
    ];
    $onDash = request()->routeIs('dashboard');
    $panel = fn ($mode) => $onDash ? "@click.prevent=\"sharet.panel('money', { mode: '{$mode}' })\"" : '';
@endphp
{{-- The Account sheet adds a history entry too, so the phone's back button closes it instead of leaving the page --}}
<div x-data="{ sheet: false }" class="md:hidden"
     x-init="$watch('sheet', v => v && history.pushState({ sheet: true }, ''))" @popstate.window="sheet = false" @account-sheet.window="sheet = true">
  <nav class="fixed inset-x-0 bottom-0 z-[1000] flex items-center justify-around border-t border-app-line bg-app-panel/95 px-2 pb-[max(env(safe-area-inset-bottom),8px)] pt-2 backdrop-blur" aria-label="App">
    @foreach (array_slice($tabs, 0, 2) as [$label, $url, $icon, $current])
      <a href="{{ $url }}" @if ($current) aria-current="page" @endif @class(['flex h-[52px] w-16 flex-col items-center justify-center gap-1 text-[11px]', 'text-app-ink font-semibold' => $current, 'text-app-muted' => ! $current])>
        <x-dynamic-component :component="'heroicon-o-'.$icon" class="h-6 w-6" /> {{ $label }}
      </a>
    @endforeach
    <a href="{{ route('dashboard', ['panel' => 'send']) }}" {!! $panel('send') !!} class="-mt-6 flex h-14 w-14 items-center justify-center rounded-full bg-app-lime text-[#050507] shadow-lg" aria-label="Send money">
      <x-heroicon-o-arrow-up-right class="h-6 w-6" />
    </a>
    @foreach (array_slice($tabs, 2) as [$label, $url, $icon, $current])
      <a href="{{ $url }}" {!! $panel('receive') !!} @class(['flex h-[52px] w-16 flex-col items-center justify-center gap-1 text-[11px]', 'text-app-ink font-semibold' => $current, 'text-app-muted' => ! $current])>
        <x-dynamic-component :component="'heroicon-o-'.$icon" class="h-6 w-6" /> {{ $label }}
      </a>
    @endforeach
    <button type="button" @click="sheet = true" class="flex h-[52px] w-16 flex-col items-center justify-center gap-1 text-[11px] text-app-muted" :class="sheet && 'text-app-ink'">
      <x-heroicon-o-user-circle class="h-6 w-6" /> Account
    </button>
  </nav>

  <div x-show="sheet" x-cloak class="fixed inset-0 z-[1001]" @keydown.escape.window="sheet && history.back()">
    <div x-show="sheet" x-transition.opacity class="absolute inset-0 bg-gray-400/50 backdrop-blur-[32px]" @click="history.back()"></div>
    <div x-show="sheet" x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
         x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full"
         class="absolute inset-x-0 bottom-0 max-h-[85vh] overflow-y-auto rounded-t-3xl bg-app-panel px-4 pb-[max(env(safe-area-inset-bottom),20px)] pt-3">
      <div class="mx-auto mb-4 h-1.5 w-10 rounded-full bg-app-ink/20"></div>
      <button type="button" @click="darkMode = !darkMode" class="mb-2 flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-app-soft">
        <x-heroicon-o-sun x-show="darkMode" class="h-5 w-5" /><x-heroicon-o-moon x-show="!darkMode" class="h-5 w-5" />
        <span x-text="darkMode ? 'Light mode' : 'Dark mode'"></span>
      </button>
      <div class="rounded-2xl bg-app-tile p-2">@include('partials.customer.account-links')</div>
    </div>
  </div>
</div>
