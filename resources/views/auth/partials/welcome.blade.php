{{-- The A01 welcome card, shown beside sign-in and sign-up on wide screens: what Sharet does, in three calm rows. --}}
<div class="flex h-full flex-col rounded-[28px] bg-app-panel p-8 shadow-[0_1px_2px_rgb(10_10_12/0.04),0_10px_30px_-18px_rgb(10_10_12/0.18)]">
  <div class="flex h-[220px] items-center justify-center rounded-[22px] bg-app-send/10 text-app-send">
    <x-heroicon-o-arrow-up-right class="h-16 w-16" stroke-width="1.6" />
  </div>
  <h2 class="app-display mt-6 text-[24px] leading-tight">Send to any phone or bank in Kenya</h2>
  <p class="mt-2 text-[15px] text-app-muted">M-PESA, any bank through its paybill, and other Sharet members for free.</p>
  <ul class="mt-6 space-y-1 rounded-[20px] bg-app-tile px-4 py-1 text-sm">
    @foreach ([
        ['arrow-up-right', 'bg-app-send/15 text-app-send', 'Send and receive', 'Your M-PESA number is your wallet number'],
        ['globe-europe-africa', 'bg-app-abroad/15 text-app-abroad', 'Abroad, next', 'Tanzania after the Kenya pilot, Uganda later'],
        ['shield-check', 'bg-app-lime text-[#050507]', 'Safe by design', 'Every payment needs your Sharet PIN'],
    ] as [$icon, $tone, $title, $text])
      <li class="flex items-center gap-3 border-b border-app-line py-3 last:border-0">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $tone }}"><x-dynamic-component :component="'heroicon-o-'.$icon" class="h-5 w-5" /></span>
        <span class="min-w-0"><span class="block font-semibold">{{ $title }}</span><span class="block text-xs text-app-muted">{{ $text }}</span></span>
      </li>
    @endforeach
  </ul>
</div>
