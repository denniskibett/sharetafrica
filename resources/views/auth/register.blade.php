{{-- Create an account (canvas A02): real names as on the ID, a real email, a real mobile number. Checked again on the
     server by App\Support\Identity (its messages show under each field). Same fields and route as before. --}}
@extends('layouts.auth')
@section('title', 'Create account')
@section('description', 'Create your Sharet account.')
@section('switch')<a href="{{ route('login') }}" class="inline-flex h-10 items-center rounded-full bg-app-tile px-4 text-sm font-semibold hover:bg-app-raised">Sign in</a>@endsection
@section('welcome')@include('auth.partials.welcome')@endsection
@section('heading', 'Create your account')
@section('lede', 'Use your names as they appear on your ID.')

@section('content')
<form method="POST" action="{{ route('register') }}" class="space-y-4">
  @csrf
  @if ($errors->count() > 1)
    <p class="flex gap-2 rounded-2xl bg-red-50 px-4 py-3 text-sm text-red-800 dark:bg-red-500/10 dark:text-red-300" role="alert">
      <x-heroicon-o-exclamation-triangle class="h-5 w-5 shrink-0" /> {{ $errors->count() }} details need fixing.
    </p>
  @endif
  <x-ui.form-input label="Full name" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Juma Jones" hint="First and last name, letters only." />
  <x-ui.form-input label="Email" name="email" type="email" :value="old('email')" required autocomplete="username" placeholder="you@example.com" />
  <x-ui.form-input label="M-PESA number" name="phone" type="tel" :value="old('phone')" required autocomplete="tel" inputmode="tel" placeholder="0712 345 678" hint="Your mobile number becomes your wallet number." />
  <x-ui.form-input label="Password" name="password" type="password" required autocomplete="new-password" />
  <x-ui.form-input label="Confirm password" name="password_confirmation" type="password" required autocomplete="new-password" />

  <button type="submit" class="mt-2 h-12 w-full rounded-full bg-app-lime text-[15px] font-semibold text-[#050507] hover:brightness-95">Create account</button>
  <p class="text-center text-sm text-app-muted">Already registered? <a href="{{ route('login') }}" class="font-semibold text-app-ink underline-offset-4 hover:underline">Sign in</a></p>
</form>
@endsection
