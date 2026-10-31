{{-- Status page for new sign-ups until an admin assigns a lane (WaitingListController). --}}
@extends('layouts.app')
@section('title', 'You are on the list')

@section('content')
<div class="mx-auto max-w-2xl py-6 text-center">
  <span class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300">
    <x-heroicon-o-clock class="h-8 w-8" />
  </span>
  <h1 class="text-3xl font-semibold text-gray-900 dark:text-white">You're on the list, {{ \Illuminate\Support\Str::before($user->name, ' ') }}.</h1>
  <p class="mx-auto mt-3 max-w-xl text-gray-500 dark:text-gray-400">
    Thank you for joining Sharet. We open access in stages so every account gets a proper start. We'll email
    <span class="font-medium text-gray-700 dark:text-gray-200">{{ $user->email }}</span> as soon as yours is ready.
  </p>

  <div class="mt-10 grid gap-4 text-left sm:grid-cols-3">
    @foreach ([['check-circle', 'emerald', 'Signed up', 'Your account exists and is secure.'], ['user-group', 'violet', 'Team review', 'A person on our team checks each sign-up.'], ['envelope', 'sky', 'Email from us', 'We email you when your account opens.']] as [$icon, $tone, $title, $text])
      <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <span @class(['mb-3 flex h-10 w-10 items-center justify-center rounded-xl',
          'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300' => $tone === 'emerald',
          'bg-violet-100 text-violet-600 dark:bg-violet-500/15 dark:text-violet-300' => $tone === 'violet',
          'bg-sky-100 text-sky-600 dark:bg-sky-500/15 dark:text-sky-300' => $tone === 'sky'])>
          <x-dynamic-component :component="'heroicon-o-'.$icon" class="h-5 w-5" />
        </span>
        <p class="font-medium text-gray-900 dark:text-white">{{ $title }}</p>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $text }}</p>
      </div>
    @endforeach
  </div>

  <p class="mt-10 text-sm text-gray-500 dark:text-gray-400">Questions? <a href="mailto:support@sharet.africa" class="font-medium text-violet-600 hover:underline dark:text-violet-400">support@sharet.africa</a></p>
</div>
@endsection
