@extends('layouts.app')

@section('title', 'Choose your lane · Sharet Africa')

@section('content')
<div class="container-fluid px-4 py-10">
    <div class="mx-auto max-w-5xl">

        <div class="mb-8 text-center">
            <p class="text-xs uppercase tracking-wider text-gray-500">Step 1 of 2</p>
            <h1 class="mt-2 text-3xl font-semibold text-gray-900">How will you use Sharet?</h1>
            <p class="mt-2 text-sm text-gray-500">Pick the lane that fits you best. You can always request a change later.</p>
        </div>

        <form method="POST" action="{{ route('onboarding.store_lane') }}">
            @csrf

            <div class="grid gap-5 md:grid-cols-2">
                @foreach($lanes as $key => $lane)
                    <label class="group relative cursor-pointer rounded-2xl border-2 border-gray-200 bg-white p-6 transition hover:border-[#D4FF3D]">
                        <input type="radio" name="lane" value="{{ $key }}" class="peer sr-only" required />

                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-[#D4FF3D]">
                                <svg class="h-6 w-6 text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="{{ $lane['icon'] }}" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">{{ $lane['title'] }}</h2>
                                <p class="mt-1 text-sm text-gray-600">{{ $lane['tagline'] }}</p>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center gap-2 text-xs font-medium text-[#D4FF3D] opacity-0 peer-checked:opacity-100">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Selected
                        </div>
                    </label>
                @endforeach
            </div>

            <div class="mt-8 flex justify-center">
                <button type="submit" class="inline-flex rounded-lg bg-[#D4FF3D] px-6 py-3 text-sm font-semibold text-gray-900 hover:brightness-95">
                    Continue
                </button>
            </div>
        </form>

    </div>
</div>
@endsection