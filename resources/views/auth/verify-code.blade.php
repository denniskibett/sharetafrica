{{-- Sign-up email code (App\Support\EmailCode). --}}
@extends('home.layouts.app')
@section('robots', 'noindex,follow')
@section('title', 'Confirm your email · Sharet Africa')

@section('content')
<section class="hero" style="padding-block: var(--space-8) var(--space-7);">
  <div class="container container--narrow">
    <div class="section-head" style="grid-template-columns: 1fr; margin-bottom: var(--space-6);">
      <div>
        <span class="eyebrow"><span class="dot" aria-hidden="true"></span>One last step</span>
        <h1 class="hero-headline" style="font-size: var(--text-4xl); margin-top: var(--space-4);">Enter the code<br/><span class="lime">we emailed you.</span></h1>
      </div>
    </div>
  </div>
</section>

<section class="snug" style="padding-top: 0;">
  <div class="container container--narrow">
    <p class="lede" style="margin-bottom: var(--space-6);">We sent a 6-digit code to <strong>{{ $email }}</strong>. It expires in {{ \App\Support\EmailCode::TTL_MINUTES }} minutes. Can't see it? Check your spam or promotions folder.</p>

    @if (session('status') === 'code-sent')
      <div style="background: rgba(212,255,61,0.08); border: 1px solid var(--rule-lime); border-radius: var(--radius); padding: var(--space-4); margin-bottom: var(--space-5); font-family: var(--font-mono); font-size: var(--text-sm); color: var(--lime);">A new code is on its way.</div>
    @endif

    <form method="POST" action="{{ route('verification.code.verify') }}" class="form-card">
      @csrf
      <div class="form-field">
        <label for="code">6-digit code</label>
        <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9 ]{6,7}" maxlength="7" required autofocus
               style="font-family: var(--font-mono); font-size: 28px; letter-spacing: .4em;" />
        @error('code')<span style="color: var(--lime); font-family: var(--font-mono); font-size: var(--text-xs);">{{ $message }}</span>@enderror
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn--primary btn--lg">Confirm email</button>
      </div>
    </form>

    <div style="display:flex;gap:var(--space-4);flex-wrap:wrap;margin-top:var(--space-5);">
      <form method="POST" action="{{ route('verification.code.resend') }}">@csrf<button type="submit" class="btn btn--ghost">Send a new code</button></form>
      <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="btn btn--ghost">Log out</button></form>
    </div>
  </div>
</section>
@endsection
