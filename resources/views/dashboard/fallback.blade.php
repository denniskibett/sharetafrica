@extends('layouts.app')

@section('content')
    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Welcome to Sharet</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
            Your account is being set up. Please complete onboarding to access your dashboard.
        </p>
        <a href="{{ route('onboarding.welcome') }}" class="mt-4 inline-flex rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:brightness-95">
            Complete onboarding
        </a>
    </div>
@endsection