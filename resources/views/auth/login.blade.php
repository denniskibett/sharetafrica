{{-- resources/views/auth/login.blade.php --}}
@extends('home.layouts.app')

@section('title', 'Sign in · Sharet Africa')
@section('description', 'Sign in to your Sharet wallet.')

@section('content')

<section class="hero" style="padding-block: var(--space-8) var(--space-7);">
  <div class="container container--narrow">
    <div class="section-head" style="grid-template-columns: 1fr; text-align: left; margin-bottom: var(--space-6);">
      <div>
        <span class="eyebrow"><span class="dot" aria-hidden="true"></span>Welcome back</span>
        <h1 class="hero-headline" style="font-size: var(--text-4xl); margin-top: var(--space-4);">
          Sign in to<br/>
          <span class="lime">your wallet.</span>
        </h1>
      </div>
    </div>
  </div>
</section>

<section class="snug" style="padding-top: 0;">
  <div class="container container--narrow">

    @if (session('status'))
      <div style="background: rgba(212,255,61,0.08); border: 1px solid var(--rule-lime); border-radius: var(--radius); padding: var(--space-4); margin-bottom: var(--space-5); font-family: var(--font-mono); font-size: var(--text-sm); color: var(--lime);">
        {{ session('status') }}
      </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="form-card">
      @csrf

      <div class="form-row form-row--full">
        <div class="form-field">
          <label for="email">Email</label>
          <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="you @ email.com" />
          @error('email')
            <span style="color: var(--lime); font-family: var(--font-mono); font-size: var(--text-xs); letter-spacing: 0.06em;">{{ $message }}</span>
          @enderror
        </div>
      </div>

      <div class="form-row form-row--full">
        <div class="form-field">
          <label for="password">Password</label>
          <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="••••••••" />
          @error('password')
            <span style="color: var(--lime); font-family: var(--font-mono); font-size: var(--text-xs); letter-spacing: 0.06em;">{{ $message }}</span>
          @enderror
        </div>
      </div>

      <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: var(--space-3); padding-block: var(--space-2);">
        <label for="remember_me" style="display: inline-flex; align-items: center; gap: var(--space-2); font-family: var(--font-mono); font-size: var(--text-xs); letter-spacing: 0.08em; text-transform: uppercase; color: var(--fg-soft); cursor: pointer;">
          <input id="remember_me" type="checkbox" name="remember" style="accent-color: var(--lime);" />
          Remember me
        </label>

        @if (Route::has('password.request'))
          <a href="{{ route('password.request') }}" style="font-family: var(--font-mono); font-size: var(--text-xs); letter-spacing: 0.08em; text-transform: uppercase; color: var(--fg-mute);">
            Forgot password?
          </a>
        @endif
      </div>

      <div class="form-actions">
        <small>New to Sharet? <a href="{{ route('register') }}" style="color: var(--lime);">Create an account →</a></small>
        <button type="submit" class="btn btn--primary btn--lg">
          Sign in
          <svg class="arrow" width="16" height="10" viewBox="0 0 14 10" fill="none" aria-hidden="true"><path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
      </div>
    </form>

  </div>
</section>

@endsection