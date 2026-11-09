{{-- Hosted pay page for an API payment request (docs/v2/INTEGRATION.md §3): who is asking, how much, and two ways to
     pay: Sharet wallet (signed in + PIN, free) or an M-PESA prompt. Sandbox requests show "pay" / "fail" buttons and
     move no money. Icons only. --}}
@extends('layouts.auth')
@section('title', 'Pay '.($company?->name ?? $payee->name))
@php
    use App\Api\PaymentInput;
    $who = $company?->name ?? $payee->name;
    $collecting = $r->order && ! $r->order->final();
@endphp
@section('heading', $r->status === 'paid' ? 'Paid' : 'Pay '.$who)
@section('lede', $r->reference ?: 'Payment request from '.$who.'.')

@section('content')
<div class="space-y-5" @if ($collecting) x-data x-init="setInterval(async () => { const s = await (await fetch('{{ route('pay-request.status', $r->token) }}')).json(); if (! s.collecting) location.reload() }, 3000)" @endif>
  <div class="rounded-3xl bg-app-tile p-5">
    <p class="text-[13px] text-app-muted">Amount</p>
    <p class="mt-1 text-[34px] font-bold leading-none tracking-tight">KES {{ number_format($r->amount / 100, 2) }}</p>
    <p class="mt-3 text-sm text-app-muted">To <span class="font-semibold text-app-ink">{{ $who }}</span> · {{ $payee->sharet_id }}</p>
    @if ($r->environment === 'test')<p class="mt-3 inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-900">Sandbox: no real money moves</p>@endif
  </div>

  @if ($errors->any())
    <div class="rounded-2xl bg-red-50 p-4 text-sm text-red-800" role="alert">{{ $errors->first() }}</div>
  @endif

  @if ($r->status === 'paid')
    <div class="flex items-center gap-3 rounded-2xl bg-lime-100 p-4 text-[15px] font-semibold text-lime-950" role="status">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l5 5L19 8"/></svg>
      Paid {{ $r->paid_at?->timezone('Africa/Nairobi')->format('j M Y, H:i') }}. You can close this page.
    </div>
  @elseif (! $r->isOpen())
    <div class="rounded-2xl bg-app-tile p-4 text-[15px] text-app-muted">This request is {{ $r->status === 'cancelled' ? 'cancelled' : 'no longer open' }}. Ask {{ $who }} for a new one.</div>
  @elseif ($collecting)
    <div class="flex items-center gap-3 rounded-2xl bg-app-tile p-4 text-[15px]" role="status">
      <svg class="animate-spin" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M12 3a9 9 0 1 0 9 9" stroke-linecap="round"/></svg>
      Check your phone and enter your M-PESA PIN. This page updates by itself.
    </div>
  @else
    @if ($r->environment === 'test')
      <form method="POST" action="{{ route('pay-request.sandbox', $r->token) }}" class="grid grid-cols-2 gap-3">
        @csrf
        <button name="outcome" value="pay" class="h-12 rounded-full bg-gray-900 text-[15px] font-semibold text-white hover:bg-gray-800">Simulate paid</button>
        <button name="outcome" value="fail" class="h-12 rounded-full bg-app-tile text-[15px] font-semibold hover:bg-app-raised">Simulate cancel</button>
      </form>
    @endif

    @if (in_array('sharet', $r->channels, true))
      <section class="rounded-3xl border border-app-ink/10 p-5">
        <h2 class="flex items-center gap-2 text-base font-semibold">
          <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-lime-300 text-lime-950"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="6" width="18" height="13" rx="3"/><path d="M16 12h2"/></svg></span>
          Pay from your Sharet wallet <span class="text-sm font-normal text-app-muted">· free</span>
        </h2>
        @auth
          @if (in_array(auth()->user()->kyc_level ?: 'none', ['none'], true))
          <div class="mt-4 space-y-3">
            <p class="text-sm text-app-muted">Verify your account before paying from your wallet: your name, M-PESA number, ID photos and a selfie. It takes a few minutes, and this page waits for you.</p>
            <a href="{{ route('profile.edit', ['return' => '/r/'.$r->token]) }}#verification" class="flex h-12 w-full items-center justify-center rounded-full bg-gray-900 text-[15px] font-semibold text-white hover:bg-gray-800">Verify now</a>
          </div>
          @elseif (! \App\Support\TransactionPin::isSet(auth()->user()))
          <form method="POST" action="{{ route('pay-request.pin', $r->token) }}" class="mt-4 space-y-3">
            @csrf
            <p class="text-sm text-app-muted">Create your Sharet PIN first. You use it to approve every payment from your wallet. Nothing is paid yet.</p>
            <x-ui.form-input label="New Sharet PIN (4 digits)" name="pin" type="password" inputmode="numeric" autocomplete="new-password" maxlength="4" pattern="[0-9]{4}" required />
            <x-ui.form-input label="Type it again" name="pin_confirmation" type="password" inputmode="numeric" autocomplete="new-password" maxlength="4" pattern="[0-9]{4}" required />
            <button type="submit" class="h-12 w-full rounded-full bg-gray-900 text-[15px] font-semibold text-white hover:bg-gray-800">Create PIN</button>
          </form>
          @else
          @if (session('pin_created'))
            <p class="mt-4 rounded-2xl bg-lime-50 p-3 text-sm text-lime-900" role="status">Your Sharet PIN is set. Enter it below to approve this payment.</p>
          @endif
          <form method="POST" action="{{ route('pay.request.sharet', $r->token) }}" class="mt-4 space-y-3">
            @csrf
            <x-ui.form-input label="Your Sharet PIN" name="pin" type="password" inputmode="numeric" autocomplete="off" maxlength="8" required />
            <button type="submit" class="h-12 w-full rounded-full bg-gray-900 text-[15px] font-semibold text-white hover:bg-gray-800">Pay KES {{ number_format($r->amount / 100, 2) }}</button>
            <p class="text-xs text-app-muted">Paying as {{ '@'.\App\Support\Handle::for(auth()->user()) }}.</p>
          </form>
          @endif
        @else
          <a href="{{ route('login') }}" class="mt-4 flex h-12 w-full items-center justify-center rounded-full bg-gray-900 text-[15px] font-semibold text-white hover:bg-gray-800">Sign in to pay with Sharet</a>
        @endauth
      </section>
    @endif

    @if (in_array('mpesa', $r->channels, true))
      <section class="rounded-3xl border border-app-ink/10 p-5">
        <h2 class="flex items-center gap-2 text-base font-semibold">
          <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-green-100 text-green-800"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/></svg></span>
          Pay with M-PESA
        </h2>
        <form method="POST" action="{{ route('pay.request.mpesa', $r->token) }}" class="mt-4 space-y-3">
          @csrf
          <x-ui.form-input label="M-PESA number" name="phone" type="tel" inputmode="tel" :value="old('phone', $r->payer_phone ? '0'.substr($r->payer_phone, 3) : '')" placeholder="0712 345 678" required />
          <button type="submit" class="h-12 w-full rounded-full bg-gray-900 text-[15px] font-semibold text-white hover:bg-gray-800">Send M-PESA prompt</button>
        </form>
      </section>
    @endif

    <p class="flex items-center gap-2 text-xs text-app-muted">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/></svg>
      Expires {{ $r->expires_at->timezone('Africa/Nairobi')->format('j M, H:i') }}. Sharet never asks for your PIN outside this page or the app.
    </p>
  @endif
</div>
@endsection
