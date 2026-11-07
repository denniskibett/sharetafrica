{{-- Consent for a connected account (docs/v2/INTEGRATION.md §6): who is asking, exactly what they may do, approve with
     the Sharet PIN or decline. Icons only. --}}
@extends('layouts.auth')
@section('title', 'Connect '.$platform)
@section('heading', $c->status === 'active' ? 'Connected to '.$platform : 'Connect '.$platform.' to your Sharet account')
@section('lede', $c->status === 'active' ? 'You can disconnect any time in Account › Connected apps.' : $platform.' would like to act for your Sharet account.')

@section('content')
<div class="space-y-5">
  @if ($errors->any())<div class="rounded-2xl bg-red-50 p-4 text-sm text-red-800" role="alert">{{ $errors->first() }}</div>@endif

  @if ($c->status === 'active')
    <div class="flex items-center gap-3 rounded-2xl bg-lime-100 p-4 text-[15px] font-semibold text-lime-950" role="status">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12l5 5L19 8"/></svg>
      Done. {{ $platform }} is connected.
    </div>
  @elseif ($c->status !== 'pending' || $c->expires_at->isPast())
    <div class="rounded-2xl bg-app-tile p-4 text-[15px] text-app-muted">This link has already been used or has expired. Ask {{ $platform }} for a new one.</div>
  @else
    <ul class="space-y-3 rounded-3xl bg-app-tile p-5 text-[15px]">
      @if (in_array('collect', $c->scopes, true))
        <li class="flex gap-3"><svg class="mt-0.5 shrink-0" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 7L7 17"/><path d="M16 17H7V8"/></svg>
          <span><b>Collect payments for you.</b> Money they collect lands in <b>your</b> Sharet wallet, never theirs.</span></li>
      @endif
      @if (in_array('payout', $c->scopes, true))
        <li class="flex gap-3"><svg class="mt-0.5 shrink-0" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17L17 7"/><path d="M8 7h9v9"/></svg>
          <span><b>Send money from your wallet</b> without asking for your PIN each time. Free to other Sharet accounts; sending to M-PESA or a bank is charged.</span></li>
      @endif
      <li class="flex gap-3"><svg class="mt-0.5 shrink-0" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z"/></svg>
        <span>You can disconnect at any time in Account › Connected apps. Your limits and our checks still apply.</span></li>
    </ul>

    @auth
      <form method="POST" action="{{ route('connect.approve', $c->token) }}" class="space-y-3">
        @csrf
        <x-ui.form-input label="Your Sharet PIN" name="pin" type="password" inputmode="numeric" autocomplete="off" maxlength="8" required />
        <button type="submit" class="h-12 w-full rounded-full bg-gray-900 text-[15px] font-semibold text-white hover:bg-gray-800">Allow {{ $platform }}</button>
      </form>
      <form method="POST" action="{{ route('connect.decline', $c->token) }}">
        @csrf
        <button type="submit" class="h-12 w-full rounded-full bg-app-tile text-[15px] font-semibold hover:bg-app-raised">No thanks</button>
      </form>
      <p class="text-xs text-app-muted">Connecting {{ '@'.\App\Support\Handle::for(auth()->user()) }} · {{ auth()->user()->sharet_id }}</p>
    @else
      <a href="{{ route('login') }}" class="flex h-12 w-full items-center justify-center rounded-full bg-gray-900 text-[15px] font-semibold text-white hover:bg-gray-800">Sign in to continue</a>
      <p class="text-center text-sm text-app-muted">New to Sharet? <a href="{{ route('register') }}" class="font-semibold text-app-ink underline-offset-4 hover:underline">Create an account</a></p>
    @endauth
  @endif
</div>
@endsection
