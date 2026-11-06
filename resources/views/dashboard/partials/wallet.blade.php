{{-- The wallet, Revolut-style: a plain white card with the account pill, the balance large and centred (it counts up
     when it changes), your @name and M-PESA number to copy, and round action buttons that open the money panel.
     The eye hides the balance and number (remembered on this device). Needs Alpine `wallet`. --}}
@php
    try {
        $mpesa = $user->phone ? preg_replace('/^254(\d{3})(\d{3})(\d{3})$/', '0$1 $2 $3', \App\Ramp\Destination::phone((string) $user->phone)) : null;
    } catch (\Throwable) {
        $mpesa = $user->phone;
    }
    $hiddenPhone = $mpesa ? substr($mpesa, 0, 4).' ••• '.substr($mpesa, -3) : null;
    // colour = meaning (Sharet UI): money in green, send lime (Sharet's own action), withdraw orange
    $actions = [
        ['receive', 'Add money', 'plus', 'bg-app-in/15 text-app-in'],
        ['send', 'Send', 'paper-airplane', 'bg-app-lime text-[#050507]'],
        ['withdraw', 'Withdraw', 'banknotes', 'bg-orange-500/15 text-orange-600 dark:text-orange-300'],
    ];
@endphp
<section x-data="{ hidden: $persist(false).as('walletHidden'), shown: wallet, copy(text, what) { navigator.clipboard?.writeText(text); sharet.toast(what + ' copied') } }"
         x-init="sharet.count(wallet, v => shown = v); $watch('wallet', w => sharet.count(w, v => shown = v))"
         class="flex min-h-[300px] flex-col items-center gap-5 rounded-2xl border border-app-line bg-app-panel p-6 text-center">

  <div class="flex w-full items-center justify-between">
    <span class="rv-pill"><span class="h-2 w-2 rounded-full bg-app-lime ring-2 ring-app-lime/30"></span>Wallet · KES</span>
    <button type="button" @click="hidden = !hidden" class="flex h-10 w-10 items-center justify-center rounded-full bg-app-tile hover:bg-app-raised" :aria-label="hidden ? 'Show balance' : 'Hide balance'">
      <x-heroicon-o-eye x-show="hidden" class="h-5 w-5" /><x-heroicon-o-eye-slash x-show="!hidden" class="h-5 w-5" />
    </button>
  </div>

  <div>
    <p class="rv-balance text-[44px] md:text-[52px]"><span class="mr-1 align-[0.45em] text-[0.42em] font-semibold tracking-normal text-app-muted">KES</span><span x-text="hidden ? '••••••' : shown">{{ $wallet }}</span></p>
    <p class="mt-1 flex items-center justify-center gap-2 text-sm text-app-muted">
      Available balance
      @if (config('rails.default') === 'simulator')<span class="rounded-full bg-app-wait/15 px-2 py-0.5 text-[11px] font-semibold text-app-wait">Test mode</span>@endif
    </p>
  </div>

  <div class="flex flex-wrap justify-center gap-2 text-sm">
    <button type="button" @click="copy('{{ '@'.$handle }}', 'Your @name')" class="rv-pill hover:bg-app-raised" aria-label="Copy your @name">
      <x-heroicon-o-at-symbol class="h-4 w-4" />{{ $handle }}<x-heroicon-o-document-duplicate class="h-4 w-4 text-app-muted" />
    </button>
    @if ($mpesa)
      <button type="button" @click="copy('{{ str_replace(' ', '', $mpesa) }}', 'Your M-PESA number')" class="rv-pill hover:bg-app-raised" aria-label="Copy your M-PESA number">
        <x-heroicon-o-device-phone-mobile class="h-4 w-4" /><span class="app-num" x-text="hidden ? @js($hiddenPhone) : @js($mpesa)">{{ $mpesa }}</span><x-heroicon-o-document-duplicate class="h-4 w-4 text-app-muted" />
      </button>
    @endif
  </div>

  <div class="mt-auto grid w-full max-w-sm grid-cols-3 gap-2 pt-2">
    @foreach ($actions as [$mode, $label, $icon, $tint])
      <button type="button" @click="sharet.panel('money', { mode: '{{ $mode }}' })" class="rv-act">
        <span class="{{ $tint }}"><x-dynamic-component :component="'heroicon-o-'.$icon" class="h-6 w-6" /></span>
        {{ $label }}
      </button>
    @endforeach
  </div>
</section>
