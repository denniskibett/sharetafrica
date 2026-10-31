{{-- resources/views/auth/verify-email.blade.php --}}
@extends('home.layouts.app')

@section('title', 'Verify your email · Sharet Africa')

@section('content')

<section class="hero" style="padding-block: var(--space-8) var(--space-7);">
  <div class="container container--narrow">
    <div class="section-head" style="grid-template-columns: 1fr; margin-bottom: var(--space-6);">
      <div>
        <span class="eyebrow"><span class="dot" aria-hidden="true"></span>Almost there</span>
        <h1 class="hero-headline" style="font-size: var(--text-4xl); margin-top: var(--space-4);">
          Verify your<br/>
          <span class="lime">email address.</span>
        </h1>
      </div>
    </div>
  </div>
</section>

<section class="snug" style="padding-top: 0;">
  <div class="container container--narrow">

    <p class="lede" style="margin-bottom: var(--space-6);">
      Thanks for signing up. Before getting started, please verify your email address by clicking the link we just emailed to you. If you didn't receive it, we will gladly send another.
    </p>

    @if (session('status') == 'verification-link-sent')
      <div style="background: rgba(212,255,61,0.08); border: 1px solid var(--rule-lime); border-radius: var(--radius); padding: var(--space-4); margin-bottom: var(--space-5); font-family: var(--font-mono); font-size: var(--text-sm); color: var(--lime);">
        A new verification link has been sent to the email address you provided during registration.
      </div>
    @endif

    <div class="form-card">
      <div class="form-actions" style="border-top: 0; padding-top: 0; justify-content: flex-start; gap: var(--space-4); flex-wrap: wrap;">

        <form method="POST" action="{{ route('verification.send') }}">
          @csrf
          <button type="submit" class="btn btn--primary btn--lg">
            Resend verification email
            <svg class="arrow" width="16" height="10" viewBox="0 0 14 10" fill="none" aria-hidden="true"><path d="M1 5h12m0 0L9 1m4 4L9 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="btn btn--ghost btn--lg">
            Log out
          </button>
        </form>

      </div>
    </div>

  </div>
</section>

@endsection