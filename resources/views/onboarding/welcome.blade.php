@extends('layouts.app')

@section('title', 'Welcome · Sharet Africa')

@section('content')
<div class="container-fluid px-4 py-12">
    <div class="mx-auto max-w-2xl text-center">

        <div class="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full bg-[#D4FF3D]">
            <svg class="h-8 w-8 text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <h1 class="text-3xl font-semibold text-gray-900">
            You're on the list.
        </h1>

        <p class="mt-4 text-base text-gray-600">
            Thank you for signing up to Sharet. We're launching market by market, and we'll send you an invite as soon as your account is ready. In the meantime, feel free to explore what Sharet can do.
        </p>

        <div class="mt-8 flex flex-wrap justify-center gap-4">
            <a href="{{ route('home') }}" class="inline-flex rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Back to homepage
            </a>
            <a href="{{ route('contact') }}" class="inline-flex rounded-lg bg-[#D4FF3D] px-5 py-2.5 text-sm font-medium text-gray-900 hover:brightness-95">
                Send us a note
            </a>
        </div>

    </div>
</div>
@endsection