{{-- Sign in (canvas A01, web version): email and password, remember me, forgot password. Same fields and route as before. --}}
@extends('layouts.auth')
@section('title', 'Sign in')
@section('description', 'Sign in to your Sharet wallet.')
@section('switch')<a href="{{ route('register') }}" class="inline-flex h-10 items-center rounded-full bg-app-tile px-4 text-sm font-semibold hover:bg-app-raised">Create account</a>@endsection
@section('welcome')@include('auth.partials.welcome')@endsection
@section('heading', 'Welcome back')
@section('lede', 'Sign in to your Sharet wallet.')

@section('content')
<form method="POST" action="{{ route('login') }}" class="space-y-4">
  @csrf
  <x-ui.form-input label="Email" name="email" type="email" :value="old('email')" required autofocus autocomplete="username" placeholder="you@example.com" />
  <x-ui.form-input label="Password" name="password" type="password" required autocomplete="current-password" />

  <div class="flex flex-wrap items-center justify-between gap-3 pt-1 text-sm">
    <label for="remember_me" class="inline-flex cursor-pointer items-center gap-2 text-app-soft">
      <input id="remember_me" type="checkbox" name="remember" class="h-5 w-5 rounded-md border-app-ink/20 text-gray-900 focus:ring-app-ink/40">
      Remember me
    </label>
    @if (Route::has('password.request'))
      <a href="{{ route('password.request') }}" class="font-semibold text-app-ink underline-offset-4 hover:underline">Forgot password?</a>
    @endif
  </div>

  <button type="submit" class="mt-2 h-12 w-full rounded-full bg-gray-900 text-[15px] font-semibold text-white hover:bg-gray-800">Sign in</button>
  <p class="text-center text-sm text-app-muted">New to Sharet? <a href="{{ route('register') }}" class="font-semibold text-app-ink underline-offset-4 hover:underline">Create an account</a></p>
</form>
@endsection
