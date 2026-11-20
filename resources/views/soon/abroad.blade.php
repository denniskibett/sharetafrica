{{-- Send abroad (canvas A13, "Send to Tanzania", next after the Kenya pilot): designed, no backend yet. Preview = the
     country list with the rollout order. SWIFT (A12) is linked from here. --}}
@extends('layouts.app')
@section('title', 'Send abroad')

@section('content')
<x-ui.coming-soon title="Send to anyone in Tanzania" icon="globe-europe-africa" tone="bg-app-abroad/15 text-app-abroad" stage="Next after the Kenya pilot"
    lede="Pay from your KES wallet; they receive Tanzanian shillings on Vodacom M-Pesa, Airtel Money, Mixx by Yas or a bank. The recipient needs no app. Uganda comes after Tanzania."
    :features="[
        ['user-circle', 'Name check first', 'We confirm the recipient\'s registered name before you pay.'],
        ['calculator', 'Rate locked for 15 minutes', 'Safaricom\'s daily rate less our margin, with the fee shown before you confirm.'],
        ['lock-closed', 'Approved with your PIN', 'Nothing leaves your wallet until you enter your Sharet PIN.'],
        ['check-badge', 'Delivered, with a reference', 'Your receipt carries the partner\'s reference.'],
    ]">
  <x-slot:today>
    <a href="{{ route('abroad.swift') }}" class="flex items-center gap-3">
      <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-app-abroad/15 text-app-abroad"><x-heroicon-o-building-library class="h-5 w-5" /></span>
      <span class="min-w-0 flex-1"><span class="block text-sm font-semibold">Paying a bank abroad?</span><span class="block text-xs text-app-muted">SWIFT transfers for suppliers, schools and family: see what is planned.</span></span>
      <x-heroicon-o-chevron-right class="h-5 w-5 shrink-0 text-app-muted" />
    </a>
  </x-slot:today>

  <p class="text-base font-semibold">Send abroad</p>
  <div class="rounded-[20px] bg-app-tile px-3.5 py-0.5">
    @foreach ([
        ['linear-gradient(135deg,#1EB53A 0 40%,#FCD116 40% 44%,#0A0A0C 44% 56%,#FCD116 56% 60%,#00A3DD 60%)', 'Tanzania', 'Vodacom M-Pesa · Airtel · Mixx by Yas · banks', 'NEXT', true],
        ['linear-gradient(#0A0A0C 0 17%,#FCDC04 17% 33%,#D90000 33% 50%,#0A0A0C 50% 67%,#FCDC04 67% 83%,#D90000 83%)', 'Uganda', 'After Tanzania', 'LATER', false],
        ['linear-gradient(#20603D 0 50%,#FAD201 50% 75%,#00A1DE 75%)', 'Rwanda', 'Later', 'LATER', false],
    ] as [$flag, $country, $note, $pill, $next])
      <div class="flex items-center gap-3 border-b border-app-line py-3 last:border-0">
        <span class="h-[17px] w-6 shrink-0 rounded-[3px]" style="background: {{ $flag }}"></span>
        <span class="min-w-0 flex-1"><span class="block text-sm font-semibold">{{ $country }}</span><span class="block truncate text-xs text-app-muted">{{ $note }}</span></span>
        <span @class(['rounded-full px-2 py-0.5 text-[10.5px] font-semibold', 'bg-app-lime text-[#050507]' => $next, 'bg-app-ink/10 text-app-soft' => ! $next])>{{ $pill }}</span>
      </div>
    @endforeach
  </div>
  <p class="flex gap-2 rounded-xl bg-app-tile px-3 py-2.5 text-xs text-app-soft"><x-heroicon-o-information-circle class="h-4 w-4 shrink-0" /> Sending is in Kenya for now. Tanzania opens after the pilot.</p>
</x-ui.coming-soon>
@endsection
