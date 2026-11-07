{{-- Pay a bank abroad by SWIFT (canvas A12): designed, no backend yet. Preview = amount and charges. Bank grade only. --}}
@extends('layouts.app')
@section('title', 'SWIFT transfer')

@php $bankGrade = auth()->user()->kyc_level === 'standard'; @endphp

@section('content')
<x-ui.coming-soon title="Pay a bank abroad by SWIFT" icon="building-library" tone="bg-app-abroad/15 text-app-abroad" stage="Coming soon · bank grade"
    lede="For suppliers, schools and family abroad. The rate is locked before you pay, larger transfers get a compliance check, and you can follow it to the receiving bank."
    :features="[
        ['identification', 'Beneficiary checked', 'The bank\'s SWIFT code is checked and the name screened before a quote.'],
        ['scale', 'You choose who pays the fees', 'OUR, SHA or BEN, with the full cost shown before you confirm.'],
        ['document-text', 'Purpose and invoice', 'Goods, school fees, family or services, with the document attached.'],
        ['map', 'Track it', 'Paid, checked, sent and credited abroad, step by step.'],
    ]">
  <x-slot:today>
    <div class="flex flex-wrap items-center gap-3">
      <a href="{{ route('abroad') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-app-soft hover:text-app-ink"><x-heroicon-o-chevron-left class="h-4 w-4" /> Send abroad</a>
      <span class="flex-1"></span>
      @unless ($bankGrade)
        <span class="text-xs text-app-muted">SWIFT needs bank grade.</span>
        <a href="{{ route('profile.edit') }}#verification" class="inline-flex h-10 items-center rounded-full bg-app-tile px-4 text-sm font-semibold hover:bg-app-raised">Upgrade</a>
      @else
        <span class="text-xs text-app-muted">You are bank grade: ready when SWIFT opens.</span>
      @endunless
    </div>
  </x-slot:today>

  <p class="text-base font-semibold">Amount</p>
  <div>
    <p class="mb-1 text-xs text-app-muted">They receive</p>
    <div class="flex h-12 items-center rounded-[14px] border-2 border-app-ink px-3.5 app-num text-lg">USD 3,000.00</div>
  </div>
  <div class="flex gap-1 rounded-xl bg-app-tile p-1 text-[13px] font-semibold"><span class="flex-1 rounded-lg bg-app-panel py-2 text-center shadow-sm">OUR</span><span class="flex-1 py-2 text-center text-app-muted">SHA</span><span class="flex-1 py-2 text-center text-app-muted">BEN</span></div>
  <div class="rounded-[20px] bg-app-tile px-3.5 text-sm">
    @foreach ([['Rate · locked 15 min', 'KES 131.67 / USD'], ['SWIFT fee', 'KES 2,500.00'], ['You pay', 'KES 397,510.00']] as $i => [$k, $v])
      <div class="flex justify-between border-b border-app-line py-2.5 last:border-0"><span @class(['text-app-muted' => $i < 2, 'font-semibold' => $i === 2])>{{ $k }}</span><span @class(['app-num', 'font-semibold' => $i === 2])>{{ $v }}</span></div>
    @endforeach
  </div>
  <span class="flex h-12 items-center justify-center rounded-[14px] bg-gray-900 text-sm font-semibold text-white">Continue</span>
</x-ui.coming-soon>
@endsection
