{{-- Administration › Scheduled jobs: cron status, what runs when, and "Run now" for a fixed list of safe commands. --}}
@extends('layouts.app')
@section('title', 'Scheduled jobs')

@section('content')
<div class="mx-auto max-w-[1100px] space-y-6">
  <div>
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Scheduled jobs</h1>
    <p class="mt-1 max-w-3xl text-sm text-gray-500 dark:text-gray-400">The server's cron runs the scheduler every minute, which starts each job when it's due. You can also run a job now; it asks for your password and is logged.</p>
  </div>

  <div @class(['flex items-center gap-3 rounded-2xl p-4 text-sm',
      'bg-emerald-50 text-emerald-900 dark:bg-emerald-500/10 dark:text-emerald-200' => $running,
      'bg-red-50 text-red-900 dark:bg-red-500/10 dark:text-red-200' => ! $running])>
    @if ($running)<x-heroicon-o-check-circle class="h-6 w-6 shrink-0" />@else<x-heroicon-o-exclamation-triangle class="h-6 w-6 shrink-0" />@endif
    <span>
      @if ($running) <b>The cron is running.</b> The scheduler last ran {{ $beat->diffForHumans() }}.
      @elseif ($beat) <b>The cron has stopped.</b> The scheduler last ran {{ $beat->diffForHumans() }}. Check the cron for the site user in CyberPanel.
      @else <b>The cron has never run.</b> Add it for the site user: <code>* * * * * cd &lt;site&gt; && php artisan schedule:run</code>
      @endif
    </span>
  </div>

  @if ($ran = session('ran'))
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]" role="status">
      <p class="flex items-center gap-2 text-sm font-semibold {{ $ran['ok'] ? 'text-emerald-700 dark:text-emerald-300' : 'text-red-700 dark:text-red-300' }}">
        @if ($ran['ok'])<x-heroicon-o-check class="h-5 w-5" />@else<x-heroicon-o-x-mark class="h-5 w-5" />@endif
        {{ $ran['label'] }} {{ $ran['ok'] ? 'finished' : 'failed' }} in {{ $ran['seconds'] }}s <code class="font-normal text-gray-500">{{ $ran['command'] }}</code>
      </p>
      <pre class="mt-3 max-h-[480px] overflow-auto rounded-xl bg-gray-900 p-4 text-xs leading-relaxed text-gray-100">{{ $ran['output'] }}</pre>
    </div>
  @endif

  <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
    <h2 class="border-b border-gray-100 px-5 py-4 text-lg font-semibold text-gray-800 dark:border-gray-800 dark:text-white/90">Run now</h2>
    <div class="divide-y divide-gray-100 dark:divide-gray-800">
      @foreach ($commands as $command => [$label, $what])
        <form method="POST" action="{{ route('admin.scheduler.run') }}" class="flex flex-col gap-2 px-5 py-3 sm:flex-row sm:items-center">
          @csrf
          <input type="hidden" name="command" value="{{ $command }}">
          <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $label }} <code class="ml-1 text-xs font-normal text-gray-500">{{ $command }}</code></p>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $what }}</p>
          </div>
          <button class="inline-flex h-10 shrink-0 items-center gap-2 rounded-full {{ $loop->first ? 'bg-gray-900 text-white hover:bg-gray-800 dark:bg-[#D4FF3D] dark:text-gray-900' : 'bg-gray-100 text-gray-800 hover:bg-gray-200 dark:bg-white/10 dark:text-gray-100' }} px-4 text-sm font-semibold">
            <x-heroicon-o-play class="h-4 w-4" /> Run now
          </button>
        </form>
      @endforeach
    </div>
  </div>

  <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
    <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">The schedule</h2>
    <pre class="mt-3 overflow-auto rounded-xl bg-gray-50 p-4 text-xs leading-relaxed text-gray-700 dark:bg-gray-900 dark:text-gray-300">{{ $schedule }}</pre>
  </div>
</div>
@endsection
