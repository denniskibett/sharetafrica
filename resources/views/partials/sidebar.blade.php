{{-- Dashboard sidebar in the system colours: the current page is highlighted in the brand primary (lime) with dark text.
     Menu comes from App\Support\DashboardNav (roles + routes); "Soon" items have no page yet.
     sidebarToggle: from tablet width up = collapsed to icons (the content and header stretch); on phones = drawer open. --}}
@php $nav = \App\Support\DashboardNav::for(auth()->user()); @endphp

{{-- Phone overlay --}}
<div x-show="sidebarToggle" x-cloak @click="sidebarToggle = false" class="fixed inset-0 z-[9998] bg-gray-900/50 md:hidden"></div>

<aside
  :class="sidebarToggle ? 'translate-x-0 md:w-[88px]' : '-translate-x-full md:w-[280px]'"
  class="fixed left-0 top-0 z-[9999] flex h-screen w-[280px] shrink-0 flex-col border-r border-gray-200 bg-white transition-all duration-300 dark:border-gray-800 dark:bg-gray-950 md:static md:translate-x-0"
>
  <div class="flex h-[72px] shrink-0 items-center border-b border-gray-200 px-5 dark:border-gray-800" :class="sidebarToggle ? 'md:justify-center md:px-0' : ''">
    <a href="{{ route('dashboard') }}" aria-label="{{ SystemHelper::appName() }} dashboard">
      <x-brand x-bind:class="sidebarToggle ? 'md:hidden' : ''" />
      <x-brand :mark-only="true" :size="36" class="hidden" x-bind:class="sidebarToggle ? 'md:flex' : ''" />
    </a>
  </div>

  <nav class="no-scrollbar flex-1 overflow-y-auto px-4 pb-8" aria-label="Dashboard">
    @foreach ($nav as $group)
      <div @if (! empty($group['soon'])) x-data="{ open: $persist(false).as('sidebarSoonOpen') }" class="mt-6 border-t border-gray-100 pt-1 dark:border-gray-800" @endif>
      @if (! empty($group['soon']))
        <button type="button" @click="open = !open" :aria-expanded="open"
                class="mb-2 mt-4 flex w-full items-center justify-between px-2 text-[11px] font-semibold uppercase tracking-wider text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300"
                :class="sidebarToggle ? 'md:justify-center' : ''">
          <span :class="sidebarToggle ? 'md:hidden' : ''">{{ $group['title'] }} ({{ count($group['items']) }})</span>
          <x-heroicon-o-chevron-down class="h-4 w-4 transition" x-bind:class="open && 'rotate-180'" />
        </button>
      @else
      <h3 class="mb-2 mt-5 px-2 text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500"
          :class="sidebarToggle ? 'md:text-center' : ''">
        <span :class="sidebarToggle ? 'md:hidden' : ''">{{ $group['title'] }}</span>
        <span x-show="sidebarToggle" x-cloak class="hidden md:inline">•••</span>
      </h3>
      @endif

      <ul class="flex flex-col gap-1" @if (! empty($group['soon'])) x-show="open" x-collapse x-cloak @endif>
        @foreach ($group['items'] as $item)
          @php $tag = $item['ready'] ? 'a' : 'span'; @endphp
          <li>
            <{{ $tag }}
              @if ($item['ready']) href="{{ $item['url'] }}" @else aria-disabled="true" @endif
              @if ($item['current']) aria-current="page" @endif
              title="{{ $item['label'] }}{{ $item['ready'] ? '' : ' (coming soon)' }}"
              @class([
                'group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors',
                'bg-primary text-gray-900' => $item['current'],
                'text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white' => $item['ready'] && ! $item['current'],
                'cursor-default text-gray-400 dark:text-gray-500' => ! $item['ready'],
              ])
              :class="sidebarToggle ? 'md:justify-center' : ''"
            >
              <x-dynamic-component :component="'heroicon-o-'.$item['icon']" @class([
                'h-6 w-6 shrink-0',
                'text-gray-900' => $item['current'],
                'text-gray-500 group-hover:text-gray-700 dark:text-gray-400 dark:group-hover:text-white' => $item['ready'] && ! $item['current'],
                'text-gray-300 dark:text-gray-600' => ! $item['ready'],
              ]) />
              <span class="flex-1 truncate" :class="sidebarToggle ? 'md:hidden' : ''">{{ $item['label'] }}</span>
              @unless ($item['ready'])
                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-gray-500 dark:bg-white/5 dark:text-gray-400"
                      :class="sidebarToggle ? 'md:hidden' : ''">Soon</span>
              @endunless
            </{{ $tag }}>
          </li>
        @endforeach
      </ul>
      </div>
    @endforeach
  </nav>
</aside>
