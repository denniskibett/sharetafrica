{{-- resources/views/auth/confirm-password.blade.php --}}
@extends('home.layouts.app')

@section('title', 'Confirm password · Sharet Africa')

@section('content')

<section class="hero" style="padding-block: var(--space-8) var(--space-7);">
  <div class="container container--narrow">
    <div class="section-head" style="grid-template-columns: 1fr; margin-bottom: var(--space-6);">
      <div>
        <span class="eyebrow"><span class="dot" aria-hidden="true"></span>Secure area</span>
        <h1 class="hero-headline" style="font-size: var(--text-4xl); margin-top: var(--space-4);">
          Confirm your<br/>
          <span class="lime">password.</span>
        </h1>
      </div>
    </div>
  </div>
</section>

<section class="snug" style="padding-top: 0;">
  <div class="container container--narrow">

    <p class="lede" style="margin-bottom: var(--space-6);">
      This is a secure area of the application. Please confirm your password before continuing.
    </p>

    <form method="POST" action="{{ route('password.confirm') }}" class="form-card">
      @csrf

      <div class="form-row form-row--full">
        <div class="form-field">
          <label for="password">Password</label>
          <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="••••••••" />
          @error('password')
            <span style="color: var(--lime); font-family: var(--font-mono); font-size: var(--text-xs);">{{ $message }}</span>
          @enderror
        </div>
      </div>

      <div class="form-actions">
        <small>&nbsp;</small>
        <button type="submit" class="btn btn--primary btn--lg">
          Confirm
          <svg class="arrow" width="16" height="10" viewBox="0 0 14 10" fill="none" aria-hidden="true"><path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
      </div>
    </form>

  </div>
</section>

@endsection