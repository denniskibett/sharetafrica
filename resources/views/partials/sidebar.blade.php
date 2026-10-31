{{-- Dashboard sidebar. Menu comes from App\Support\DashboardNav (roles + routes); "Soon" items have no page yet.
     sidebarToggle: on large screens = collapsed to icons; on phones = drawer open. --}}
@php
    $tones = [
        'violet'  => 'bg-violet-100 text-violet-600 dark:bg-violet-500/15 dark:text-violet-300',
        'emerald' => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300',
        'sky'     => 'bg-sky-100 text-sky-600 dark:bg-sky-500/15 dark:text-sky-300',
        'amber'   => 'bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300',
        'indigo'  => 'bg-indigo-100 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300',
        'teal'    => 'bg-teal-100 text-teal-600 dark:bg-teal-500/15 dark:text-teal-300',
        'rose'    => 'bg-rose-100 text-rose-600 dark:bg-rose-500/15 dark:text-rose-300',
        'orange'  => 'bg-orange-100 text-orange-600 dark:bg-orange-500/15 dark:text-orange-300',
        'slate'   => 'bg-slate-100 text-slate-600 dark:bg-slate-500/20 dark:text-slate-300',
    ];
    $nav = \App\Support\DashboardNav::for(auth()->user());
@endphp

{{-- Phone overlay --}}
<div x-show="sidebarToggle" x-cloak @click="sidebarToggle = false" class="fixed inset-0 z-[9998] bg-gray-900/50 lg:hidden"></div>

<aside
  :class="sidebarToggle ? 'translate-x-0 lg:w-[88px]' : '-translate-x-full lg:w-[280px]'"
  class="fixed left-0 top-0 z-[9999] flex h-screen w-[280px] shrink-0 flex-col border-r border-gray-200 bg-white transition-all duration-300 dark:border-gray-800 dark:bg-gray-950 lg:static lg:translate-x-0"
>
  <div class="flex h-[72px] items-center px-5" :class="sidebarToggle ? 'lg:justify-center lg:px-0' : ''">
    <a href="{{ route('dashboard') }}" aria-label="{{ SystemHelper::appName() }} dashboard">
      <x-brand x-bind:class="sidebarToggle ? 'lg:hidden' : ''" />
      <x-brand :mark-only="true" :size="36" class="hidden" x-bind:class="sidebarToggle ? 'lg:flex' : ''" />
    </a>
  </div>

  <nav class="no-scrollbar flex-1 overflow-y-auto px-4 pb-8" aria-label="Dashboard">
    @foreach ($nav as $group)
      <h3 class="mb-2 mt-5 px-2 text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500"
          :class="sidebarToggle ? 'lg:text-center' : ''">
        <span :class="sidebarToggle ? 'lg:hidden' : ''">{{ $group['title'] }}</span>
        <span x-show="sidebarToggle" x-cloak class="hidden lg:inline">•••</span>
      </h3>

      <ul class="flex flex-col gap-1">
        @foreach ($group['items'] as $item)
          @php $tag = $item['ready'] ? 'a' : 'span'; @endphp
          <li>
            <{{ $tag }}
              @if ($item['ready']) href="{{ $item['url'] }}" @else aria-disabled="true" @endif
              @if ($item['current']) aria-current="page" @endif
              title="{{ $item['label'] }}{{ $item['ready'] ? '' : ' (coming soon)' }}"
              @class([
                'group flex items-center gap-3 rounded-xl px-2 py-2 text-sm font-medium transition-colors',
                'bg-gray-100 text-gray-900 dark:bg-white/[0.06] dark:text-white' => $item['current'],
                'text-gray-700 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.04] dark:hover:text-white' => $item['ready'] && ! $item['current'],
                'cursor-default text-gray-400 dark:text-gray-500' => ! $item['ready'],
              ])
              :class="sidebarToggle ? 'lg:justify-center' : ''"
            >
              <span @class([
                'flex h-9 w-9 shrink-0 items-center justify-center rounded-lg transition',
                $tones[$item['tone']] ?? $tones['slate'],
                'opacity-50' => ! $item['ready'],
                'ring-2 ring-gray-900/10 dark:ring-white/20' => $item['current'],
              ])>
                <x-dynamic-component :component="'heroicon-o-'.$item['icon']" class="h-5 w-5" />
              </span>
              <span class="flex-1 truncate" :class="sidebarToggle ? 'lg:hidden' : ''">{{ $item['label'] }}</span>
              @unless ($item['ready'])
                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-gray-500 dark:bg-white/5 dark:text-gray-400"
                      :class="sidebarToggle ? 'lg:hidden' : ''">Soon</span>
              @endunless
            </{{ $tag }}>
          </li>
        @endforeach
      </ul>
    @endforeach
  </nav>
</aside>
