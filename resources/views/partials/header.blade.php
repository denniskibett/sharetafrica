{{-- Dashboard top bar: menu toggle, logo on phones, light/dark switch, notifications, account menu. --}}
@php $user = auth()->user(); @endphp
<header class="sticky top-0 z-[999] flex h-[72px] w-full items-center border-b border-gray-200 bg-white/90 px-4 backdrop-blur dark:border-gray-800 dark:bg-gray-900/90 md:px-6">
  <div class="flex w-full items-center gap-3">
    <button type="button" @click.stop="sidebarToggle = !sidebarToggle"
            class="flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-100 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-white/5"
            :aria-label="sidebarToggle ? 'Close menu' : 'Open menu'">
      <x-heroicon-o-bars-3-bottom-left class="h-5 w-5" />
    </button>

    <a href="{{ route('dashboard') }}" class="lg:hidden" aria-label="Dashboard"><x-brand :size="28" /></a>

    <div class="ml-auto flex items-center gap-2 sm:gap-3">
      {{-- Light / dark --}}
      <button type="button" @click="darkMode = !darkMode"
              class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white"
              :aria-label="darkMode ? 'Switch to light mode' : 'Switch to dark mode'">
        <x-heroicon-o-sun class="hidden h-5 w-5 text-amber-400 dark:block" />
        <x-heroicon-o-moon class="h-5 w-5 text-indigo-500 dark:hidden" />
      </button>

      {{-- Notifications: none are produced yet, so an honest empty state --}}
      <div class="relative" x-data="{ open: false }" @click.outside="open = false">
        <button type="button" @click="open = !open" aria-label="Notifications"
                class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white">
          <x-heroicon-o-bell class="h-5 w-5" />
        </button>
        <div x-show="open" x-cloak x-transition
             class="absolute right-0 mt-3 w-[min(320px,calc(100vw-2rem))] rounded-2xl border border-gray-200 bg-white p-5 text-center shadow-lg dark:border-gray-800 dark:bg-gray-900">
          <span class="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-sky-100 text-sky-600 dark:bg-sky-500/15 dark:text-sky-300">
            <x-heroicon-o-bell class="h-5 w-5" />
          </span>
          <p class="text-sm font-medium text-gray-800 dark:text-white/90">You're all caught up</p>
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Payment and account alerts will show here.</p>
        </div>
      </div>

      {{-- Account --}}
      <div class="relative" x-data="{ open: false }" @click.outside="open = false">
        <button type="button" @click="open = !open" class="flex items-center gap-2 rounded-full py-1 pl-1 pr-2 hover:bg-gray-100 dark:hover:bg-white/5">
          <span class="flex h-9 w-9 items-center justify-center overflow-hidden rounded-full bg-violet-100 text-sm font-bold text-violet-700 dark:bg-violet-500/15 dark:text-violet-300">
            @if ($user->avatar)
              <img src="{{ asset('storage/'.$user->avatar) }}" alt="" class="h-full w-full object-cover">
            @else
              {{ \Illuminate\Support\Str::of($user->name)->explode(' ')->filter()->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('') }}
            @endif
          </span>
          <span class="hidden max-w-[140px] truncate text-sm font-medium text-gray-700 dark:text-gray-300 sm:block">{{ $user->name }}</span>
          <x-heroicon-o-chevron-down class="h-4 w-4 text-gray-400 transition" x-bind:class="open && 'rotate-180'" />
        </button>

        <div x-show="open" x-cloak x-transition
             class="absolute right-0 mt-3 w-64 rounded-2xl border border-gray-200 bg-white p-3 shadow-lg dark:border-gray-800 dark:bg-gray-900">
          <div class="px-3 pb-3">
            <p class="truncate text-sm font-medium text-gray-800 dark:text-white/90">{{ $user->name }}</p>
            <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
          </div>
          <div class="flex flex-col gap-1 border-y border-gray-100 py-2 dark:border-gray-800">
            <a href="{{ route('profile.show') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">
              <x-heroicon-o-user-circle class="h-5 w-5 text-rose-500" /> Profile
            </a>
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">
              <x-heroicon-o-cog-6-tooth class="h-5 w-5 text-slate-500" /> Settings
            </a>
          </div>
          <form method="POST" action="{{ route('logout') }}" class="pt-2">
            @csrf
            <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10">
              <x-heroicon-o-arrow-left-start-on-rectangle class="h-5 w-5" /> Sign out
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</header>
