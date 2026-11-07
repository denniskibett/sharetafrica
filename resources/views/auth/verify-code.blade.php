{{-- Sign-up email code (App\Support\EmailCode, canvas A02 step 4). Same field, routes and resend as before. --}}
@extends('layouts.auth')
@section('title', 'Confirm your email')
@section('own-status', '1')
@section('icon')<x-heroicon-o-envelope-open class="h-7 w-7" />@endsection
@section('heading', 'Confirm your email')

@section('content')
<p class="-mt-4 mb-6 text-[15px] leading-relaxed text-app-muted">We sent a 6-digit code to <strong class="text-app-ink">{{ $email }}</strong>. It expires in {{ \App\Support\EmailCode::TTL_MINUTES }} minutes. Can't see it? Check your spam or promotions folder.</p>

@if (session('status') === 'code-sent')
  <p class="mb-5 flex gap-2 rounded-2xl bg-app-in/10 px-4 py-3 text-sm text-app-in" role="status"><x-heroicon-o-check-circle class="h-5 w-5 shrink-0" /> A new code is on its way.</p>
@endif

<form method="POST" action="{{ route('verification.code.verify') }}" class="space-y-4">
  @csrf
  <div>
    <label for="code" class="mb-1.5 block text-[13px] font-medium text-app-muted">6-digit code</label>
    <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9 ]{6,7}" maxlength="7" required autofocus
           @error('code') aria-invalid="true" aria-describedby="code-error" @enderror
           class="app-num h-16 w-full rounded-2xl border-0 bg-app-tile px-4 text-center text-[28px] tracking-[0.4em] text-app-ink focus:outline-none focus:ring-2 @error('code') ring-2 ring-red-500 focus:ring-red-500 @else focus:ring-app-ink/70 @enderror">
    @error('code')<p id="code-error" class="mt-1.5 text-xs text-red-600">{{ $message }}</p>@enderror
  </div>
  <button type="submit" class="h-12 w-full rounded-full bg-gray-900 text-[15px] font-semibold text-white hover:bg-gray-800">Confirm email</button>
</form>

<div class="mt-2 space-y-1">
  <form method="POST" action="{{ route('verification.code.resend') }}">@csrf<button type="submit" class="h-12 w-full rounded-full text-[15px] font-semibold text-app-ink hover:bg-app-tile">Send a new code</button></form>
  <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="h-11 w-full rounded-full text-sm font-medium text-app-muted hover:text-app-ink">Log out</button></form>
</div>
@endsection
