{{-- resources/views/auth/forgot-password.blade.php --}}
@extends('home.layouts.app')

@section('title', 'Reset password · Sharet Africa')

@section('content')

<section class="hero" style="padding-block: var(--space-8) var(--space-7);">
  <div class="container container--narrow">
    <div class="section-head" style="grid-template-columns: 1fr; margin-bottom: var(--space-6);">
      <div>
        <span class="eyebrow"><span class="dot" aria-hidden="true"></span>Account recovery</span>
        <h1 class="hero-headline" style="font-size: var(--text-4xl); margin-top: var(--space-4);">
          Forgot your<br/>
          <span class="lime">password?</span>
        </h1>
      </div>
    </div>
  </div>
</section>

<section class="snug" style="padding-top: 0;">
  <div class="container container--narrow">

    <p class="lede" style="margin-bottom: var(--space-6);">
      No problem. Enter your email address and we will send you a password reset link so you can choose a new one.
    </p>

    @if (session('status'))
      <div style="background: rgba(212,255,61,0.08); border: 1px solid var(--rule-lime); border-radius: var(--radius); padding: var(--space-4); margin-bottom: var(--space-5); font-family: var(--font-mono); font-size: var(--text-sm); color: var(--lime);">
        {{ session('status') }}
      </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="form-card">
      @csrf

      <div class="form-row form-row--full">
        <div class="form-field">
          <label for="email">Email</label>
          <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus placeholder="you @ email.com" />
          @error('email')
            <span style="color: var(--lime); font-family: var(--font-mono); font-size: var(--text-xs);">{{ $message }}</span>
          @enderror
        </div>
      </div>

      <div class="form-actions">
        <small>Remembered it? <a href="{{ route('login') }}" style="color: var(--lime);">Sign in →</a></small>
        <button type="submit" class="btn btn--primary btn--lg">
          Send reset link
          <svg class="arrow" width="16" height="10" viewBox="0 0 14 10" fill="none" aria-hidden="true"><path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
      </div>
    </form>

  </div>
</section>

@endsection