{{-- Previews of every Sharet email (App\Mail\SharetMail::previews) with sample data. --}}
@extends('layouts.app')
@section('title', 'Email designs')

@section('content')
<div x-data="{ current: @js($emails[0]['key'] ?? null), dark: false }">
  <div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Email designs</h1>
    <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">Every email Sharet sends, shown with sample data. They go out through the email settings in Integrations.</p>
  </div>

  <div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
    <ul class="flex flex-col gap-2">
      @foreach ($emails as $email)
        <li>
          <button type="button" @click="current = @js($email['key'])"
                  class="flex w-full items-start gap-3 rounded-2xl border bg-white p-4 text-left dark:bg-white/[0.03]"
                  :class="current === @js($email['key']) ? 'border-rose-400 ring-4 ring-rose-500/10' : 'border-gray-200 hover:border-gray-300 dark:border-gray-800'">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-rose-100 text-rose-600 dark:bg-rose-500/15 dark:text-rose-300"><x-heroicon-o-envelope class="h-5 w-5" /></span>
            <span class="min-w-0">
              <span class="block text-sm font-medium text-gray-900 dark:text-white">{{ $email['name'] }}</span>
              <span class="block truncate text-xs text-gray-500 dark:text-gray-400">Subject: {{ $email['subject'] }}</span>
            </span>
          </button>
        </li>
      @endforeach
    </ul>

    <div class="rounded-2xl border border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-white/[0.03]">
      <div class="mb-3 flex justify-end gap-1">
        <button type="button" @click="dark = false" class="rounded-md px-3 py-1.5 text-sm font-medium" :class="!dark ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'text-gray-500'">Light</button>
        <button type="button" @click="dark = true" class="rounded-md px-3 py-1.5 text-sm font-medium" :class="dark ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'text-gray-500'">Dark</button>
      </div>
      <iframe :src="'{{ url('admin/emails') }}/' + current + (dark ? '?theme=dark' : '')" title="Email preview" class="h-[760px] w-full rounded-xl border-0"></iframe>
    </div>
  </div>
</div>
@endsection
