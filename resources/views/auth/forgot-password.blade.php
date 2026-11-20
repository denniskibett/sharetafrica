{{-- Forgot password: email a reset link. Same field and route as before. --}}
@extends('layouts.auth')
@section('title', 'Reset password')
@section('switch')<a href="{{ route('login') }}" class="inline-flex h-10 items-center rounded-full bg-app-tile px-4 text-sm font-semibold hover:bg-app-raised">Sign in</a>@endsection
@section('icon')<x-heroicon-o-key class="h-7 w-7" />@endsection
@section('heading', 'Forgot your password?')
@section('lede', 'No problem. Enter your email and we will send you a link to choose a new one.')

@section('content')
<form method="POST" action="{{ route('password.email') }}" class="space-y-4">
  @csrf
  <x-ui.form-input label="Email" name="email" type="email" :value="old('email')" required autofocus autocomplete="username" placeholder="you@example.com" />
  <button type="submit" class="mt-2 h-12 w-full rounded-full bg-gray-900 text-[15px] font-semibold text-white hover:bg-gray-800">Send reset link</button>
  <p class="text-center text-sm text-app-muted">Remembered it? <a href="{{ route('login') }}" class="font-semibold text-app-ink underline-offset-4 hover:underline">Sign in</a></p>
</form>
@endsection
