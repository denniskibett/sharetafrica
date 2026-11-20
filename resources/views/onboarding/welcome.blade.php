{{-- Waiting list welcome (canvas A01/A02 "You're in"): what happened, what's next, two calm ways out. --}}
@extends('layouts.app')
@section('title', 'Welcome')

@section('content')
<div class="mx-auto flex max-w-xl flex-col items-center gap-6 py-6 text-center md:py-12">
  <span class="flex h-20 w-20 items-center justify-center rounded-full bg-app-lime text-[#050507]"><x-heroicon-o-check class="h-10 w-10" stroke-width="2.4" /></span>
  <div>
    <h1>You're on the list</h1>
    <p class="mx-auto mt-2 max-w-md text-[15px] text-app-muted">Thank you for signing up to Sharet. We're opening market by market, and we'll email you an invite as soon as your account is ready.</p>
  </div>

  <ul class="w-full rounded-[20px] bg-app-panel px-4 py-1 text-left text-sm">
    <li class="flex items-center gap-3 border-b border-app-line py-3">
      <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-app-in/15 text-app-in"><x-heroicon-o-check class="h-5 w-5" /></span>
      <span class="min-w-0 flex-1"><span class="block font-semibold">Account created</span><span class="block text-xs text-app-muted">Done</span></span>
    </li>
    <li class="flex items-center gap-3 border-b border-app-line py-3">
      <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-app-lime text-[#050507]"><x-heroicon-o-envelope class="h-5 w-5" /></span>
      <span class="min-w-0 flex-1"><span class="block font-semibold">Your invite</span><span class="block text-xs text-app-muted">We email you when your market opens</span></span>
      <span class="rounded-full bg-app-lime px-2 py-0.5 text-[11px] font-semibold text-[#050507]">NEXT</span>
    </li>
    <li class="flex items-center gap-3 py-3">
      <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-app-tile text-app-muted"><x-heroicon-o-identification class="h-5 w-5" /></span>
      <span class="min-w-0 flex-1"><span class="block font-semibold">Verify your ID</span><span class="block text-xs text-app-muted">About 2 minutes, after the invite</span></span>
    </li>
  </ul>

  <div class="flex w-full flex-col gap-2 sm:flex-row sm:justify-center">
    <a href="{{ route('contact') }}" class="inline-flex h-12 items-center justify-center rounded-full bg-app-lime px-6 text-[15px] font-semibold text-[#050507] hover:brightness-95">Send us a note</a>
    <a href="{{ route('home') }}" class="inline-flex h-12 items-center justify-center rounded-full bg-app-tile px-6 text-[15px] font-semibold text-app-ink hover:bg-app-raised">Back to the website</a>
  </div>
</div>
@endsection
