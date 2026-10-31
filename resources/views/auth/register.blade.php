{{-- resources/views/auth/register.blade.php --}}
@extends('home.layouts.app')

@section('title', 'Create account · Sharet Africa')
@section('description', 'Create your Sharet account.')

@section('content')

<section class="hero" style="padding-block: var(--space-8) var(--space-7);">
  <div class="container container--narrow">
    <div class="section-head" style="grid-template-columns: 1fr; text-align: left; margin-bottom: var(--space-6);">
      <div>
        <span class="eyebrow"><span class="dot" aria-hidden="true"></span>Create your wallet</span>
        <h1 class="hero-headline" style="font-size: var(--text-4xl); margin-top: var(--space-4);">
          Create your<br/>
          <span class="lime">Sharet account.</span>
        </h1>
      </div>
    </div>
  </div>
</section>

<section class="snug" style="padding-top: 0;">
  <div class="container container--narrow">

    <form method="POST" action="{{ route('register') }}" class="form-card">
      @csrf

      <div class="form-row">
        <div class="form-field">
          <label for="name">Full name</label>
          <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Amina Wanjiru" />
          @error('name')
            <span style="color: var(--lime); font-family: var(--font-mono); font-size: var(--text-xs);">{{ $message }}</span>
          @enderror
        </div>

        <div class="form-field">
          <label for="email">Email</label>
          <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username" placeholder="you @ email.com" />
          @error('email')
            <span style="color: var(--lime); font-family: var(--font-mono); font-size: var(--text-xs);">{{ $message }}</span>
          @enderror
        </div>
      </div>

      <div class="form-row">
        <div class="form-field">
          <label for="phone">Phone number</label>
          <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required placeholder="+254 712 XXX XXX" />
          @error('phone')
            <span style="color: var(--lime); font-family: var(--font-mono); font-size: var(--text-xs);">{{ $message }}</span>
          @enderror
        </div>

        <div class="form-field">
          <label for="role">Role</label>
          <select id="role" name="role" required>
            <option value="borrower">Borrower</option>
            <option value="teller">Teller</option>
            <option value="broker">Broker</option>
            <option value="admin">Admin</option>
          </select>
          @error('role')
            <span style="color: var(--lime); font-family: var(--font-mono); font-size: var(--text-xs);">{{ $message }}</span>
          @enderror
        </div>
      </div>

      <div class="form-row">
        <div class="form-field">
          <label for="password">Password</label>
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
        <small>Already registered? <a href="{{ route('login') }}" style="color: var(--lime);">Sign in →</a></small>
        <button type="submit" class="btn btn--primary btn--lg">
          Create account
          <svg class="arrow" width="16" height="10" viewBox="0 0 14 10" fill="none" aria-hidden="true"><path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
      </div>
    </form>

  </div>
</section>

@endsection