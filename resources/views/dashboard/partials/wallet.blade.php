{{-- The wallet (no page of its own), drawn as a Sharet card that fills its half of the row: logo, @name, balance,
     holder, and the three money actions inside it as round icon buttons (the transaction-row style), each opening the
     money panel. @name and M-PESA number copy with one tap; the eye hides the balance and number (remembered on this
     device). Deliberately no card number or chip: it is a wallet, not a payment card. Needs Alpine `wallet`. --}}
@php
    try {
        $mpesa = $user->phone ? preg_replace('/^254(\d{3})(\d{3})(\d{3})$/', '0$1 $2 $3', \App\Ramp\Destination::phone((string) $user->phone)) : null;
    } catch (\Throwable) {
        $mpesa = $user->phone;
    }
    $hiddenPhone = $mpesa ? substr($mpesa, 0, 4).' ••• '.substr($mpesa, -3) : null;
    $actions = [['send', 'Send', 'paper-airplane', 'bg-[#050507] text-[#D4FF3D]'], ['receive', 'Receive', 'arrow-down-tray', 'bg-black/10'], ['withdraw', 'Withdraw', 'banknotes', 'bg-black/10']];
@endphp
<section x-data="{ hidden: $persist(false).as('walletHidden'), copy(text, what) { navigator.clipboard?.writeText(text); sharet.toast(what + ' copied') } }"
         class="relative isolate flex min-h-[300px] flex-col gap-5 overflow-hidden rounded-[24px] bg-gradient-to-br from-[#D4FF3D] to-[#B8E62A] p-6 text-[#050507] shadow-[0_20px_40px_-24px_rgba(5,5,7,0.45)]">
  {{-- the Sharet mark, large and faint --}}
  <svg viewBox="0 0 182 154" aria-hidden="true" class="pointer-events-none absolute -right-10 -top-6 -z-10 h-64 w-auto opacity-[0.07]">
    <polygon fill="#050507" points="0,0 138.69,0 181.73,35.87 138.69,71.74 0,71.74"></polygon>
    <polygon fill="#050507" points="181.73,81.3 43.04,81.3 0,117.17 43.04,153.04 181.73,153.04"></polygon>
  </svg>

  <div class="flex items-start justify-between gap-3">
    <div>
      <img src="{{ asset('images/brand/sharet-logo-one-colour.svg') }}" alt="Sharet" class="h-6 w-auto">
      <p class="app-mono mt-2 text-[11px] uppercase tracking-[0.18em] opacity-70">Wallet · KES</p>
    </div>
    <button type="button" @click="hidden = !hidden" class="flex h-10 w-10 items-center justify-center rounded-full bg-black/10 hover:bg-black/15" :aria-label="hidden ? 'Show balance' : 'Hide balance'">
      <x-heroicon-o-eye x-show="hidden" class="h-5 w-5" /><x-heroicon-o-eye-slash x-show="!hidden" class="h-5 w-5" />
    </button>
  </div>

  <div class="flex-1">
    <p class="text-xs opacity-60">Balance</p>
    <p class="text-[40px] font-bold leading-tight tabular-nums" x-text="hidden ? '••••••' : wallet">{{ $wallet }}</p>
    <p class="app-mono mt-1 flex flex-wrap items-center gap-2 text-xs uppercase tracking-[0.14em]">
      <span class="truncate">{{ $user->name }}</span>
      @if (config('rails.default') === 'simulator')<span class="rounded-full bg-black/10 px-2 py-0.5 tracking-wider">Test mode</span>@endif
    </p>
    <div class="mt-4 flex flex-wrap gap-2 text-sm">
      <button type="button" @click="copy('{{ '@'.$handle }}', 'Your @name')" class="flex items-center gap-2 rounded-full bg-black/10 py-1.5 pl-3 pr-2 hover:bg-black/15" aria-label="Copy your @name">
        <x-heroicon-o-at-symbol class="h-4 w-4" /><span class="font-semibold">{{ $handle }}</span><x-heroicon-o-document-duplicate class="h-4 w-4 opacity-60" />
      </button>
      @if ($mpesa)
        <button type="button" @click="copy('{{ str_replace(' ', '', $mpesa) }}', 'Your M-PESA number')" class="flex items-center gap-2 rounded-full bg-black/10 py-1.5 pl-3 pr-2 hover:bg-black/15" aria-label="Copy your M-PESA number">
          <x-heroicon-o-device-phone-mobile class="h-4 w-4" /><span class="app-num" x-text="hidden ? @js($hiddenPhone) : @js($mpesa)">{{ $mpesa }}</span><x-heroicon-o-document-duplicate class="h-4 w-4 opacity-60" />
        </button>
      @endif
    </div>
  </div>

  <div class="grid grid-cols-3 gap-2 border-t border-black/10 pt-4">
    @foreach ($actions as [$mode, $label, $icon, $tint])
      <button type="button" @click="sharet.panel('money', { mode: '{{ $mode }}' })" class="group flex flex-col items-center gap-2 rounded-2xl py-1 text-sm font-semibold">
        <span class="flex h-14 w-14 items-center justify-center rounded-full {{ $tint }} transition group-hover:scale-105">
          <x-dynamic-component :component="'heroicon-o-'.$icon" class="h-6 w-6" />
        </span>
        {{ $label }}
      </button>
    @endforeach
  </div>
</section>
