{{-- resources/views/auth/reset-password.blade.php --}}
@extends('home.layouts.app')

@section('title', 'Set a new password · Sharet Africa')

@section('content')

<section class="hero" style="padding-block: var(--space-8) var(--space-7);">
  <div class="container container--narrow">
    <div class="section-head" style="grid-template-columns: 1fr; margin-bottom: var(--space-6);">
      <div>
        <span class="eyebrow"><span class="dot" aria-hidden="true"></span>Reset your password</span>
        <h1 class="hero-headline" style="font-size: var(--text-4xl); margin-top: var(--space-4);">
          Choose a<br/>
          <span class="lime">new password.</span>
        </h1>
      </div>
    </div>
  </div>
</section>

<section class="snug" style="padding-top: 0;">
  <div class="container container--narrow">

    <form method="POST" action="{{ route('password.store') }}" class="form-card">
      @csrf
      <input type="hidden" name="token" value="{{ $request->route('token') }}">

      <div class="form-row form-row--full">
        <div class="form-field">
          <label for="email">Email</label>
          <input id="email" name="email" type="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" placeholder="you @ email.com" />
          @error('email')
            <span style="color: var(--lime); font-family: var(--font-mono); font-size: var(--text-xs);">{{ $message }}</span>
          @enderror
        </div>
      </div>

      <div class="form-row">
        <div class="form-field">
          <label for="password">New password</label>
          <input id="password" name="password" type="password" required autocomplete="new-password" placeholder="••••••••" />
          @error('password')
            <span style="color: var(--lime); font-family: var(--font-mono); font-size: var(--text-xs);">{{ $message }}</span>
          @enderror
        </div>

        <div class="form-field">
          <label for="password_confirmation">Confirm password</label>
          <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" placeholder="••••••••" />
          @error('password_confirmation')
            <span style="color: var(--lime); font-family: var(--font-mono); font-size: var(--text-xs);">{{ $message }}</span>
          @enderror
        </div>
      </div>

      <div class="form-actions">
        <small>&nbsp;</small>
        <button type="submit" class="btn btn--primary btn--lg">
          Reset password
          <svg class="arrow" width="16" height="10" viewBox="0 0 14 10" fill="none" aria-hidden="true"><path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
      </div>
    </form>

  </div>
</section>

@endsection