{{-- USD virtual card (canvas A11): designed, no backend yet. Preview = the card home. Bank grade is needed, so the
     member's real level is shown with the way to upgrade. --}}
@extends('layouts.app')
@section('title', 'Card')

@php $bankGrade = auth()->user()->kyc_level === 'standard'; @endphp

@section('content')
<x-ui.coming-soon title="A USD card for paying online" icon="credit-card" tone="bg-app-pay/15 text-app-pay"
    lede="A virtual card in US dollars for bank-grade members. You top it up from your KES wallet at a rate locked before you pay, and every payment notifies you."
    :features="[
        ['arrows-right-left', 'Top up from your wallet', 'See the rate and what the card gets before you confirm.'],
        ['bell-alert', 'A note on every payment', 'You are told the moment the card is used.'],
        ['pause-circle', 'Freeze in one tap', 'Stops all payments at once. Unfreeze when you are ready.'],
        ['eye-slash', 'Details behind your PIN', 'The full number and CVV show only after your Sharet PIN.'],
    ]">
  <x-slot:today>
    <div class="flex flex-wrap items-center gap-3">
      <span @class(['flex h-10 w-10 shrink-0 items-center justify-center rounded-xl', 'bg-app-in/15 text-app-in' => $bankGrade, 'bg-app-lime text-[#050507]' => ! $bankGrade])><x-heroicon-o-shield-check class="h-5 w-5" /></span>
      @if ($bankGrade)
        <span class="min-w-0 flex-1"><span class="block text-sm font-semibold">You are bank grade</span><span class="block text-xs text-app-muted">You can have a card as soon as cards open.</span></span>
      @else
        <span class="min-w-0 flex-1"><span class="block text-sm font-semibold">Cards need bank grade</span><span class="block text-xs text-app-muted">KRA PIN, address, work and income, and a signature.</span></span>
        <a href="{{ route('profile.edit') }}#verification" class="inline-flex h-10 items-center rounded-full bg-app-tile px-4 text-sm font-semibold hover:bg-app-raised">Upgrade</a>
      @endif
    </div>
  </x-slot:today>

  <p class="text-base font-semibold">Cards</p>
  <div class="flex h-[190px] flex-col justify-between rounded-[18px] bg-[linear-gradient(135deg,#0A0A0C_0_60%,#2A2A30)] p-[18px] text-[#F4F4F0]">
    <div class="flex items-center justify-between"><img src="{{ asset('images/brand/sharet-logo-colour-on-dark.svg') }}" alt="" class="h-5 w-auto"><span class="text-xs font-semibold text-white/70">USD</span></div>
    <p class="font-mono text-[17px] tracking-[0.12em]">5321 •••• •••• 0471</p>
    <div class="flex items-end justify-between text-xs"><span class="font-semibold tracking-wide">JUMA JONES</span><span class="text-white/70">09/29</span></div>
  </div>
  <div class="rounded-[20px] bg-app-tile px-3.5 text-sm">
    <div class="flex justify-between border-b border-app-line py-2.5"><span class="text-app-muted">Card balance</span><span class="app-num">USD 64.20</span></div>
    <div class="flex items-center justify-between py-2.5"><span class="text-app-muted">Status</span><span class="rounded-full bg-app-in/15 px-2 py-0.5 text-[11px] font-semibold text-app-in">ACTIVE</span></div>
  </div>
  <div class="flex gap-2">
    <span class="flex h-12 flex-1 items-center justify-center gap-2 rounded-[14px] border border-app-ink/15 text-sm font-semibold"><x-heroicon-o-eye class="h-4 w-4" /> Details</span>
    <span class="flex h-12 flex-1 items-center justify-center rounded-[14px] bg-gray-900 text-sm font-semibold text-white">Top up</span>
  </div>
</x-ui.coming-soon>
@endsection
