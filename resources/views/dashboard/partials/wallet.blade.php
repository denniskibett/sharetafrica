{{-- The wallet, as designed on the canvas (A05, "kept calm"): on phones it continues the ink top bar edge to edge
     with a rounded bottom; wider screens show it as an ink card in its half of the row.
     Inside the ink: "Available balance" with an eye that hides the figure (and masks the number), the balance
     (counts up when it changes), then the M-PESA number (it is the Sharet wallet number) and @name as two pills
     that copy on tap. Below it, on white: Add, Send, Request, Withdraw as outline circles. Needs Alpine `wallet`. --}}
@php
    try {
        $mpesa = $user->phone ? preg_replace('/^254(\d{3})(\d{3})(\d{3})$/', '0$1 $2 $3', \App\Ramp\Destination::phone((string) $user->phone)) : null;
    } catch (\Throwable) {
        $mpesa = $user->phone;
    }
    $hiddenPhone = $mpesa ? substr($mpesa, 0, 4).' ••• '.substr($mpesa, -3) : null;
    $actions = [
        ['Add', 'plus', "{ mode: 'receive', from: 'mpesa' }"],
        ['Send', 'arrow-up-right', "{ mode: 'send' }"],
        ['Request', 'arrow-down-left', "{ mode: 'receive', from: 'sharet' }"],
        ['Withdraw', 'arrow-down-tray', "{ mode: 'withdraw' }"],
    ];
    $pill = 'inline-flex items-center gap-2 rounded-full bg-white/[0.08] px-3 py-1.5 text-[13px] text-white/80 transition hover:bg-white/[0.14]';
@endphp
<div class="flex flex-col gap-5"
     x-data="{ hidden: $persist(false).as('walletHidden'), shown: wallet, copy(text, what) { navigator.clipboard?.writeText(text); sharet.toast(what + ' copied') } }"
     x-init="sharet.count(wallet, v => shown = v); $watch('wallet', w => sharet.count(w, v => shown = v))">
  <section class="-mx-4 -mt-5 flex flex-col gap-5 rounded-b-[28px] bg-[#0A0A0C] px-4 pb-6 pt-3 text-[#F4F4F0] md:mx-0 md:mt-0 md:min-h-[230px] md:rounded-[24px] md:p-6">
    <div>
      <p class="flex items-center gap-2 text-[13px] text-white/60">
        Available balance
        <button type="button" @click="hidden = !hidden" class="flex h-7 w-7 items-center justify-center rounded-full bg-white/[0.08] text-white hover:bg-white/15" :aria-label="hidden ? 'Show balance' : 'Hide balance'" :aria-pressed="hidden">
          <x-heroicon-o-eye x-show="!hidden" class="h-4 w-4" /><x-heroicon-o-eye-slash x-show="hidden" x-cloak class="h-4 w-4" />
        </button>
        @if (config('rails.default') === 'simulator')<span class="rounded-full bg-orange-400/15 px-2 py-0.5 text-[11px] font-semibold text-orange-300">Test mode</span>@endif
      </p>
      <p class="rv-balance mt-1 text-[40px] text-white md:text-[46px]" x-text="hidden ? '••••••' : 'KES ' + shown">KES {{ $wallet }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
      @if ($mpesa)
        <button type="button" @click="copy('{{ str_replace(' ', '', $mpesa) }}', 'Your M-PESA number')" class="{{ $pill }}" aria-label="Copy your M-PESA number (your Sharet wallet number)">
          <img src="{{ asset('images/partners/mpesa.svg') }}" alt="M-PESA" class="h-[11px] w-auto opacity-80 brightness-0 invert">
          <span class="app-num" x-text="hidden ? @js($hiddenPhone) : @js($mpesa)">{{ $mpesa }}</span>
          <x-heroicon-o-document-duplicate class="h-4 w-4 opacity-60" />
        </button>
      @endif
      <button type="button" @click="copy('{{ '@'.$handle }}', 'Your @name')" class="{{ $pill }}" aria-label="Copy your @name">
        <x-heroicon-o-at-symbol class="h-4 w-4" />{{ $handle }}<x-heroicon-o-document-duplicate class="h-4 w-4 opacity-60" />
      </button>
    </div>
  </section>

  <div class="grid grid-cols-4 gap-2 md:px-2">
    @foreach ($actions as [$label, $icon, $payload])
      <button type="button" @click="sharet.panel('money', {{ $payload }})" class="flex flex-col items-center gap-2 text-[13px] font-medium text-app-ink">
        <span class="flex h-[52px] w-[52px] items-center justify-center rounded-full border border-app-ink/15 transition hover:border-app-ink/40 hover:bg-app-tile"><x-dynamic-component :component="'heroicon-o-'.$icon" class="h-5 w-5" /></span>
        {{ $label }}
      </button>
    @endforeach
  </div>
</div>
