{{-- Requests, splits and regular collections (canvas A08): designed, no backend yet. Preview = the requests inbox.
     Getting paid by @name already works (the money panel's Receive), so that is offered first. --}}
@extends('layouts.app')
@section('title', 'Requests')

@php $handle = \App\Support\Handle::for(auth()->user()); @endphp

@section('content')
<x-ui.coming-soon title="Request and split" icon="arrow-down-left" tone="bg-app-in/15 text-app-in"
    lede="Ask someone on Sharet for money, split a bill with friends, or let a shop or landlord collect a set amount. Nothing moves until the payer approves with their PIN."
    :features="[
        ['hand-raised', 'Request money', 'Ask an @name for an amount and say what it is for. It expires after 48 hours or 7 days.'],
        ['users', 'Split a bill', 'One request per person, and you see who has paid.'],
        ['arrow-path', 'Regular collections', 'Allow a shop or landlord to collect up to a limit. Cancel any time.'],
        ['link', 'Payment link', 'Share a link: M-PESA payers get a prompt on their phone.'],
    ]">
  <x-slot:today>
    <div class="flex flex-wrap items-center gap-3">
      <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-app-lime text-[#050507]"><x-heroicon-o-at-symbol class="h-5 w-5" /></span>
      <span class="min-w-0 flex-1"><span class="block text-sm font-semibold">Works today: get paid with {{ '@'.$handle }}</span><span class="block text-xs text-app-muted">Other Sharet members can send to your @name, free and instant.</span></span>
      <a href="{{ route('dashboard', ['panel' => 'receive']) }}" class="inline-flex h-10 items-center rounded-full bg-app-tile px-4 text-sm font-semibold hover:bg-app-raised">Receive</a>
    </div>
  </x-slot:today>

  <p class="text-base font-semibold">Requests</p>
  <div class="flex gap-1 rounded-xl bg-app-tile p-1 text-[13px] font-semibold"><span class="flex-1 rounded-lg bg-app-panel py-2 text-center shadow-sm">To pay (2)</span><span class="flex-1 py-2 text-center text-app-muted">Sent (1)</span></div>
  <div class="rounded-[20px] bg-app-tile px-3.5 py-0.5">
    @foreach ([['JJ', 'Juma asks KES 600', 'Lunch on Friday · 47 h left'], ['KC', 'Kamau Co-op asks KES 1,200', 'Chama · 6 days left']] as [$initials, $what, $note])
      <div class="flex items-center gap-3 border-b border-app-line py-2.5 last:border-0">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-900 text-xs font-semibold text-app-lime">{{ $initials }}</span>
        <span class="min-w-0"><span class="block text-sm font-semibold">{{ $what }}</span><span class="block text-xs text-app-muted">{{ $note }}</span></span>
      </div>
    @endforeach
  </div>
  <div class="flex gap-2">
    <span class="flex h-12 flex-1 items-center justify-center rounded-[14px] border border-app-ink/15 text-sm font-semibold">Decline</span>
    <span class="flex h-12 flex-1 items-center justify-center rounded-[14px] bg-gray-900 text-sm font-semibold text-white">Pay KES 600</span>
  </div>
  <p class="flex gap-2 rounded-xl bg-app-tile px-3 py-2.5 text-xs text-app-soft"><x-heroicon-o-lock-closed class="h-4 w-4 shrink-0" /> Only a message until they approve. Decline or expiry: nothing moves.</p>
</x-ui.coming-soon>
@endsection
