{{-- Choose a new password (from the emailed link). Same fields, token and route as before. --}}
@extends('layouts.auth')
@section('title', 'Set a new password')
@section('icon')<x-heroicon-o-lock-closed class="h-7 w-7" />@endsection
@section('heading', 'Choose a new password')
@section('lede', 'Use at least 8 characters. You will sign in with it from now on.')

@section('content')
<form method="POST" action="{{ route('password.store') }}" class="space-y-4">
  @csrf
  <input type="hidden" name="token" value="{{ $request->route('token') }}">
  <x-ui.form-input label="Email" name="email" type="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
  <x-ui.form-input label="New password" name="password" type="password" required autocomplete="new-password" />
  <x-ui.form-input label="Confirm password" name="password_confirmation" type="password" required autocomplete="new-password" />
  <button type="submit" class="mt-2 h-12 w-full rounded-full bg-gray-900 text-[15px] font-semibold text-white hover:bg-gray-800">Reset password</button>
</form>
@endsection
