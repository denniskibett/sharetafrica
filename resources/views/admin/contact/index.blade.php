{{-- Website contact form (enquiries table), newest first. The team copy email has reply-to set to the sender. --}}
@extends('layouts.app')
@section('title', 'Enquiries')

@section('content')
<div class="mb-6">
  <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Enquiries</h1>
  <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">From the form on sharet.africa/contact. Each one is also emailed to {{ config('mail.contact_to') }}.</p>
</div>

<div class="flex flex-col gap-3">
  @forelse ($messages as $m)
    <article x-data="{ open: false }" class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
      <button type="button" @click="open = !open" class="flex w-full flex-wrap items-center gap-3 p-4 text-left">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-sky-100 text-sm font-bold text-sky-700 dark:bg-sky-500/15 dark:text-sky-300">{{ mb_substr(($m->user?->name ?? 'Unknown'), 0, 1) }}</span>
        <span class="min-w-0 flex-1">
          <span class="block font-medium text-gray-900 dark:text-white">{{ ($m->user?->name ?? 'Unknown') }} <span class="font-normal text-gray-500">· {{ ($m->user?->email ?? '') }}</span></span>
          <span class="block truncate text-sm text-gray-500 dark:text-gray-400">{{ \App\Mail\SharetMail::AUDIENCES[$m->audience] ?? $m->audience }} — {{ \Illuminate\Support\Str::limit($m->brief, 90) }}</span>
        </span>
        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $m->created_at->timezone('Africa/Nairobi')->format('j M, H:i') }}</span>
        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium capitalize text-gray-600 dark:bg-white/5 dark:text-gray-300">{{ $m->status }}</span>
      </button>
      <div x-show="open" x-cloak class="border-t border-gray-100 p-4 text-sm dark:border-gray-800">
        <dl class="grid gap-x-6 gap-y-2 sm:grid-cols-[140px_1fr]">
          <dt class="text-gray-500">Business</dt><dd class="text-gray-800 dark:text-gray-200">{{ $m->business ?: '—' }}</dd>
          <dt class="text-gray-500">Phone</dt><dd class="text-gray-800 dark:text-gray-200">{{ $m->user?->phone ?: '—' }}</dd>
          <dt class="text-gray-500">Country</dt><dd class="text-gray-800 dark:text-gray-200">{{ $m->user?->country ?: '—' }}</dd>
        </dl>
        <p class="mt-4 whitespace-pre-line text-gray-800 dark:text-gray-200">{{ $m->brief }}</p>
        <a href="mailto:{{ ($m->user?->email ?? '') }}?subject={{ rawurlencode('Re: your note to Sharet') }}" class="mt-4 inline-flex h-10 items-center gap-2 rounded-lg bg-violet-600 px-4 text-sm font-medium text-white hover:bg-violet-700"><x-heroicon-o-arrow-uturn-left class="h-4 w-4" /> Reply</a>
      </div>
    </article>
  @empty
    <p class="rounded-2xl border border-dashed border-gray-300 p-10 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">No messages yet.</p>
  @endforelse
</div>
<div class="mt-6">{{ $messages->links() }}</div>
@endsection
