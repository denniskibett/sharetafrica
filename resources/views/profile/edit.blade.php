@extends('layouts.app')

@section('content')
  <div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Settings</h1>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Your details, password and account.</p>
  </div>

  <div class="grid gap-6 xl:grid-cols-2">
    <section class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
      @include('profile.partials.update-profile-information-form')
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6">
      @include('profile.partials.update-password-form')
    </section>

    <section class="rounded-2xl border border-red-200 bg-white p-5 dark:border-red-500/20 dark:bg-white/[0.03] sm:p-6 xl:col-span-2">
      @include('profile.partials.delete-user-form')
    </section>
  </div>
@endsection
