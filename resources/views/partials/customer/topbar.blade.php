{{-- Customer app top bar. Phones: avatar, greeting, notifications (like the canvas mobile home).
     Wider screens: page actions "Add money" and "Send money", notifications, account menu. --}}
@php
    $user = auth()->user();
    $hour = (int) now()->timezone('Africa/Nairobi')->format('G');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $initials = \App\Support\Table::person($user)['initials'];
    $first = \Illuminate\Support\Str::before(trim($user->name), ' ') ?: $user->name;
@endphp
<header class="sticky top-0 z-[999] flex h-[72px] shrink-0 items-center gap-3 border-b border-white/[0.08] bg-[#0A0A0C]/90 px-4 backdrop-blur md:px-8">
  <a href="{{ route('profile.show') }}" class="flex min-w-0 items-center gap-3 md:hidden" aria-label="Your profile">
    <span class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-full bg-[#232328] text-[15px] font-semibold">
      @if ($user->avatar)<img src="{{ asset('storage/'.$user->avatar) }}" alt="" class="h-full w-full object-cover">@else{{ $initials }}@endif
    </span>
    <span class="min-w-0"><span class="block text-[13px] text-[#80807A]">{{ $greeting }}</span><span class="block truncate text-base font-semibold">{{ $first }}</span></span>
  </a>

  <div class="ml-auto flex items-center gap-2 md:gap-3">
    {{-- Shown as "coming soon" until the deposit / transfer pages exist --}}
    @if (Route::has('wallet.deposit'))
      <a href="{{ route('wallet.deposit') }}" class="hidden h-[46px] items-center rounded-xl border border-white/[0.16] px-[18px] text-sm font-semibold hover:bg-white/[0.04] md:flex">Add money</a>
    @else
      <span title="Coming soon" aria-disabled="true" class="hidden h-[46px] cursor-default items-center rounded-xl border border-white/[0.08] px-[18px] text-sm font-semibold text-[#5A5A55] md:flex">Add money</span>
    @endif
    @if (Route::has('wallet.transfer'))
      <a href="{{ route('wallet.transfer') }}" class="hidden h-[46px] items-center rounded-xl bg-[#D4FF3D] px-[18px] text-sm font-semibold text-[#050507] hover:bg-[#E2FF73] md:flex">Send money</a>
    @else
      <span title="Coming soon" aria-disabled="true" class="hidden h-[46px] cursor-default items-center gap-2 rounded-xl bg-[#D4FF3D]/30 px-[18px] text-sm font-semibold text-[#050507] md:flex">Send money <span class="app-mono rounded bg-black/20 px-1.5 py-0.5 text-[10px] uppercase">Soon</span></span>
    @endif
    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
      <button type="button" @click="open = !open" aria-label="Notifications" class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#18181C] hover:bg-[#232328]"><x-heroicon-o-bell class="h-5 w-5" /></button>
      <div x-show="open" x-cloak x-transition class="absolute right-0 mt-3 w-[min(320px,calc(100vw-2rem))] rounded-2xl border border-white/[0.08] bg-[#18181C] p-5 text-center shadow-xl">
        <p class="text-sm font-semibold">You're all caught up</p>
        <p class="mt-1 text-xs text-[#80807A]">Payment and account alerts will show here.</p>
      </div>
    </div>
    <div class="relative hidden md:block" x-data="{ open: false }" @click.outside="open = false">
      <button type="button" @click="open = !open" class="flex items-center gap-2 rounded-full py-1 pl-1 pr-2 hover:bg-white/[0.04]" aria-label="Account menu">
        <span class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full bg-[#232328] text-sm font-semibold">
          @if ($user->avatar)<img src="{{ asset('storage/'.$user->avatar) }}" alt="" class="h-full w-full object-cover">@else{{ $initials }}@endif
        </span>
        <x-heroicon-o-chevron-down class="h-4 w-4 text-[#80807A]" />
      </button>
      <div x-show="open" x-cloak x-transition class="absolute right-0 mt-3 w-60 rounded-2xl border border-white/[0.08] bg-[#18181C] p-2 shadow-xl">
        <p class="truncate px-3 pb-2 pt-1 text-sm font-semibold">{{ $user->name }}<span class="block truncate text-xs font-normal text-[#80807A]">{{ $user->email }}</span></p>
        @include('partials.customer.account-links')
      </div>
    </div>
  </div>
</header>
