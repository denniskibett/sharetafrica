{{-- Dashboard top bar: menu toggle, logo on phones, light/dark switch, notifications, account menu. --}}
@php $user = auth()->user(); @endphp
<header class="sticky top-0 z-[999] flex h-[72px] w-full shrink-0 items-center border-b border-gray-200 bg-white/90 px-4 backdrop-blur dark:border-gray-800 dark:bg-gray-900/90 md:px-6">
  <div class="flex w-full items-center gap-3">
    <button type="button" @click.stop="sidebarToggle = !sidebarToggle"
            class="flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-100 dark:border-gray-800 dark:text-gray-400 dark:hover:bg-white/5"
            :aria-label="sidebarToggle ? 'Close menu' : 'Open menu'">
      <x-heroicon-o-bars-3-bottom-left class="h-5 w-5" />
    </button>

    <a href="{{ route('dashboard') }}" class="md:hidden" aria-label="Dashboard"><x-brand :size="28" /></a>

    {{-- Header search: jump to a menu page or open a payment, person, business, support case or ledger account.
         Data: SearchController (GET /search), scoped to what this person may open. ⌘K / Ctrl+K or "/" focuses it. --}}
    <div class="relative hidden sm:block sm:w-72 lg:w-96"
         x-data="{
            q: '', results: [], active: 0, open: false, loading: false, seq: 0, isMac: /Mac|iPhone|iPad/.test(navigator.platform),
            async search() {
                const n = ++this.seq;
                this.loading = true;
                try {
                    const r = await fetch(@js(route('search')) + '?q=' + encodeURIComponent(this.q.trim()), { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
                    const d = await r.json();
                    if (n !== this.seq) return; // a newer search finished first
                    this.results = d.results || []; this.active = 0; this.open = true;
                } catch (e) { if (n === this.seq) this.results = []; }
                if (n === this.seq) this.loading = false;
            },
            focus() { this.open = true; if (!this.results.length) this.search() },
            move(step) {
                if (!this.results.length) return;
                this.active = (this.active + step + this.results.length) % this.results.length;
                this.$nextTick(() => this.$refs.list?.querySelector('[aria-selected=true]')?.scrollIntoView({ block: 'nearest' }));
            },
            go(i = this.active) { const r = this.results[i]; if (!r) return; this.close(); location.href = r.url },
            close() { this.open = false; this.q = ''; this.results = []; this.$refs.q?.blur() },
            shortcut(e) {
                const typing = /INPUT|TEXTAREA|SELECT/.test(e.target.tagName) || e.target.isContentEditable;
                if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); this.$refs.q.focus() }
                else if (e.key === '/' && !typing) { e.preventDefault(); this.$refs.q.focus() }
            },
            icon(name) { const t = this.$refs.icons?.content.querySelector('[data-icon=&quot;' + name + '&quot;]'); return t ? t.innerHTML : '' },
         }"
         @keydown.window="shortcut($event)" @click.outside="open = false">
      <form role="search" @submit.prevent="go()">
        <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
        <input x-ref="q" x-model="q" type="search" placeholder="Search or jump to…" autocomplete="off" aria-label="Search"
               role="combobox" aria-controls="header-search-results" :aria-expanded="open && results.length > 0"
               @focus="focus()" @input.debounce.150ms="search()"
               @keydown.down.prevent="move(1)" @keydown.up.prevent="move(-1)" @keydown.enter.prevent="go()" @keydown.escape.prevent="close()"
               class="h-10 w-full rounded-lg border border-gray-200 bg-gray-50 pl-10 pr-20 text-sm text-gray-800 placeholder:text-gray-400 focus:border-violet-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-violet-500/10 dark:border-gray-800 dark:bg-white/[0.03] dark:text-white/90 dark:placeholder:text-white/30 dark:focus:bg-gray-900 [&::-webkit-search-cancel-button]:hidden">
        <x-heroicon-o-arrow-path x-show="loading" x-cloak class="pointer-events-none absolute right-14 top-1/2 h-4 w-4 -translate-y-1/2 animate-spin text-gray-400" />
        <kbd class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 rounded-md border border-gray-200 bg-white px-1.5 py-0.5 font-sans text-[11px] text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400"><span x-text="isMac ? '⌘' : 'Ctrl '">⌘</span>K</kbd>
      </form>

      {{-- Results, grouped under small headings --}}
      <div x-show="open && (results.length || (q.trim().length >= 2 && !loading))" x-cloak
           x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
           class="absolute left-0 right-0 top-full z-[1000] mt-2 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-lg dark:border-gray-800 dark:bg-gray-900 lg:right-auto lg:w-[430px]">
        <ul id="header-search-results" x-ref="list" role="listbox" class="max-h-[60vh] overflow-y-auto p-2">
          <template x-for="(r, i) in results" :key="r.group + r.url + i">
            <li role="option" :aria-selected="i === active">
              <p x-show="i === 0 || results[i - 1].group !== r.group" x-text="r.group" class="px-3 pb-1 pt-3 text-xs font-semibold uppercase tracking-wider text-gray-400"></p>
              <a :href="r.url" @mouseenter="active = i" @click.prevent="go(i)"
                 :class="i === active ? 'bg-violet-50 text-violet-700 dark:bg-violet-500/15 dark:text-violet-200' : 'text-gray-700 dark:text-gray-300'"
                 class="flex items-center gap-3 rounded-lg px-3 py-2.5">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-100 dark:bg-white/5" x-html="icon(r.icon) || icon('magnifying-glass')"></span>
                <span class="min-w-0 flex-1">
                  <span class="block truncate text-sm font-medium" x-text="r.label"></span>
                  <span class="block truncate text-xs text-gray-500 dark:text-gray-400" x-text="r.sub"></span>
                </span>
              </a>
            </li>
          </template>
          <li x-show="!loading && q.trim().length >= 2 && !results.length" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Nothing found for “<span x-text="q"></span>”.</li>
        </ul>
        <div class="flex items-center gap-4 border-t border-gray-200 px-4 py-2.5 text-xs text-gray-400 dark:border-gray-800">
          <span><kbd class="font-sans">↑</kbd> <kbd class="font-sans">↓</kbd> to move</span><span><kbd class="font-sans">Enter</kbd> to open</span><span class="ml-auto"><kbd class="font-sans">Esc</kbd> to close</span>
        </div>
      </div>

      {{-- Icons the results can use, rendered once on the server --}}
      <template x-ref="icons">
        @foreach (\App\Http\Controllers\SearchController::icons($user) as $ic)
          <span data-icon="{{ $ic }}"><x-dynamic-component :component="'heroicon-o-'.$ic" class="h-4 w-4" /></span>
        @endforeach
      </template>
    </div>

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
              <x-heroicon-o-user-circle class="h-5 w-5 text-slate-500" /> Profile
            </a>
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">
              <x-heroicon-o-cog-6-tooth class="h-5 w-5 text-slate-500" /> Account settings
            </a>
            {{-- System for platform admins; Business settings for each tenant (merchant / business company) --}}
            @if ($user->can('admin.system') && Route::has('admin.system.index'))
              <a href="{{ route('admin.system.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">
                <x-heroicon-o-adjustments-horizontal class="h-5 w-5 text-slate-500" /> System
              </a>
            @endif
            @if (in_array($user->intent, ['merchant', 'business'], true) || $user->company_id)
              <a href="{{ route('settings.company') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">
                <x-heroicon-o-building-storefront class="h-5 w-5 text-slate-500" /> Business settings
              </a>
            @endif
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
