{{-- Dashboard sidebar in the system colours: the current page is highlighted in the brand primary (lime) with dark text.
     Menu comes from App\Support\DashboardNav (roles + routes); "Soon" items have no page yet.
     sidebarToggle: from tablet width up = collapsed to icons (the content and header stretch); on phones = drawer open. --}}
@php $nav = \App\Support\DashboardNav::for(auth()->user()); @endphp

{{-- Phone overlay --}}
<div x-show="sidebarToggle" x-cloak @click="sidebarToggle = false" class="fixed inset-0 z-[9998] bg-gray-900/50 md:hidden"></div>

<aside
  :class="sidebarToggle ? 'translate-x-0 md:w-[88px]' : '-translate-x-full md:w-[280px]'"
  class="sh-staff-side fixed left-0 top-0 z-[9999] flex h-screen w-[280px] shrink-0 flex-col bg-[#0A0A0C] text-[#C9C9C2] transition-all duration-300 md:static md:translate-x-0"
>
  <div class="flex h-[72px] shrink-0 items-center px-5" :class="sidebarToggle ? 'md:justify-center md:px-0' : ''">
    <a href="{{ route('dashboard') }}" aria-label="{{ SystemHelper::appName() }} dashboard">
      <span class="sh-lg !text-[18px]" x-bind:class="sidebarToggle ? 'md:hidden' : ''">sharet<i>.</i></span>
      <x-brand :mark-only="true" :size="36" class="hidden" x-bind:class="sidebarToggle ? 'md:flex' : ''" />
    </a>
  </div>

  <nav class="no-scrollbar flex-1 overflow-y-auto px-4 pb-8" aria-label="Dashboard">
    @foreach ($nav as $group)
      <div @if (! empty($group['soon'])) x-data="{ open: $persist(false).as('sidebarSoonOpen') }" class="mt-6 border-t border-white/10 pt-1" @endif>
      @if (! empty($group['soon']))
        <button type="button" @click="open = !open" :aria-expanded="open"
                class="mb-2 mt-4 flex w-full items-center justify-between px-2 sh-mono text-[10px] uppercase tracking-[.14em] text-[#6E6E68] hover:text-[#C9C9C2]"
                :class="sidebarToggle ? 'md:justify-center' : ''">
          <span :class="sidebarToggle ? 'md:hidden' : ''">{{ $group['title'] }} ({{ count($group['items']) }})</span>
          <x-heroicon-o-chevron-down class="h-4 w-4 transition" x-bind:class="open && 'rotate-180'" />
        </button>
      @else
      <h3 class="sh-mono mb-1 mt-5 px-2 text-[10px] uppercase tracking-[.14em] text-[#6E6E68]"
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
                'group flex min-h-[38px] items-center gap-3 rounded-[10px] px-3 py-2 text-[13.5px] font-medium transition-colors',
                'bg-[#D4FF3D] font-semibold text-[#0A0A0C]' => $item['current'],
                'text-[#C9C9C2] hover:bg-white/[.06] hover:text-white' => $item['ready'] && ! $item['current'],
                'cursor-default text-[#6E6E68]' => ! $item['ready'],
              ])
              :class="sidebarToggle ? 'md:justify-center' : ''"
            >
              <x-dynamic-component :component="'heroicon-o-'.$item['icon']" @class([
                'h-[18px] w-[18px] shrink-0',
                'text-[#0A0A0C]' => $item['current'],
                'text-[#8A8A84] group-hover:text-white' => $item['ready'] && ! $item['current'],
                'text-[#4A4A46]' => ! $item['ready'],
              ]) />
              <span class="flex-1 truncate" :class="sidebarToggle ? 'md:hidden' : ''">{{ $item['label'] }}</span>
              @unless ($item['ready'])
                <span class="sh-mono rounded-full bg-white/[.06] px-2 py-0.5 text-[9.5px] uppercase tracking-[.1em] text-[#8A8A84]"
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
