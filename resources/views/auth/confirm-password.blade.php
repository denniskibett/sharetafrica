{{-- Confirm your password before a sensitive step. Same field and route as before. --}}
@extends('layouts.auth')
@section('title', 'Confirm password')
@section('icon')<x-heroicon-o-shield-check class="h-7 w-7" />@endsection
@section('heading', 'Confirm it\'s you')
@section('lede', 'This is a secure area. Enter your password to continue.')

@section('content')
<form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
  @csrf
  <x-ui.form-input label="Password" name="password" type="password" required autofocus autocomplete="current-password" />
  <button type="submit" class="mt-2 h-12 w-full rounded-full bg-gray-900 text-[15px] font-semibold text-white hover:bg-gray-800">Confirm</button>
</form>
@endsection
