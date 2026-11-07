{{-- Choose your lane (step 1 of 2), as option tiles (canvas A03 "Which document do you have?"). Same field and route. --}}
@extends('layouts.app')
@section('title', 'Choose your lane')

@php
    // colour by meaning: people blue, shops violet, trade amber, developers ink
    $tones = ['individual' => 'bg-app-send/15 text-app-send', 'merchant' => 'bg-app-pay/15 text-app-pay', 'business' => 'bg-app-trade/15 text-app-trade', 'developer' => 'bg-gray-900 text-white dark:bg-white/10'];
@endphp

@section('content')
<div class="mx-auto max-w-2xl py-2 md:py-8">
  <p class="text-[13px] font-medium text-app-muted">Step 1 of 2</p>
  <h1 class="mt-1">How will you use Sharet?</h1>
  <p class="mt-2 text-[15px] text-app-muted">Pick the lane that fits you best. You can ask to change it later.</p>

  <form method="POST" action="{{ route('onboarding.store_lane') }}" class="mt-6">
    @csrf
    <div class="grid gap-3 sm:grid-cols-2">
      @foreach ($lanes as $key => $lane)
        <label class="relative block cursor-pointer">
          <input type="radio" name="lane" value="{{ $key }}" class="peer sr-only" required @checked(old('lane') === $key) />
          <span class="flex h-full items-start gap-3 rounded-[20px] bg-app-panel p-4 shadow-[0_1px_2px_rgb(10_10_12/0.04),0_10px_30px_-18px_rgb(10_10_12/0.18)] ring-2 ring-transparent transition hover:ring-app-ink/20 peer-checked:ring-app-ink peer-focus-visible:ring-app-ink/60">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $tones[$key] ?? 'bg-app-tile text-app-ink' }}">
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $lane['icon'] }}" /></svg>
            </span>
            <span class="min-w-0 flex-1">
              <span class="block font-semibold">{{ $lane['title'] }}</span>
              <span class="mt-0.5 block text-sm text-app-muted">{{ $lane['tagline'] }}</span>
            </span>
          </span>
          <span class="pointer-events-none absolute right-4 top-4 hidden h-6 w-6 items-center justify-center rounded-full bg-[#0A0A0C] text-app-lime peer-checked:flex"><x-heroicon-o-check class="h-4 w-4" stroke-width="2.4" /></span>
        </label>
      @endforeach
    </div>
    @error('lane')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror

    <div class="mt-6 flex justify-end">
      <button type="submit" class="h-12 w-full rounded-full bg-gray-900 px-8 text-[15px] font-semibold text-white hover:bg-gray-800 sm:w-auto">Continue</button>
    </div>
  </form>
</div>
@endsection
