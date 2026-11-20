{{-- Email link verification (when the email code is switched off). Same routes as before. --}}
@extends('layouts.auth')
@section('title', 'Verify your email')
@section('own-status', '1')
@section('icon')<x-heroicon-o-envelope class="h-7 w-7" />@endsection
@section('heading', 'Check your email')
@section('lede', 'Thanks for signing up. Tap the link we just emailed you to confirm your address. Can\'t see it? Check your spam or promotions folder, or we can send another.')

@section('content')
@if (session('status') == 'verification-link-sent')
  <p class="mb-5 flex gap-2 rounded-2xl bg-app-in/10 px-4 py-3 text-sm text-app-in" role="status"><x-heroicon-o-check-circle class="h-5 w-5 shrink-0" /> A new link is on its way to the email you signed up with.</p>
@endif
<div class="space-y-2">
  <form method="POST" action="{{ route('verification.send') }}">
    @csrf
    <button type="submit" class="h-12 w-full rounded-full bg-gray-900 text-[15px] font-semibold text-white hover:bg-gray-800">Resend verification email</button>
  </form>
  <form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit" class="h-12 w-full rounded-full text-[15px] font-semibold text-app-ink hover:bg-app-tile">Log out</button>
  </form>
</div>
@endsection
