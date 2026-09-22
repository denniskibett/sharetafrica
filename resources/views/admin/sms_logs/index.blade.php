@extends('layouts.app')

@section('title', 'SMS Logs')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-screen-2xl">

    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fas fa-sms text-indigo-600 dark:text-indigo-400"></i>
                SMS Logs
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Every send attempt — status, provider response, and cost.
            </p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Today Total</p>
            <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ $stats['today_total'] }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Delivered</p>
            <p class="text-2xl font-bold text-emerald-600">{{ $stats['today_delivered'] }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Failed / Rejected</p>
            <p class="text-2xl font-bold text-rose-600">{{ $stats['today_failed'] }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Skipped</p>
            <p class="text-2xl font-bold text-amber-600">{{ $stats['today_skipped'] }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Today Cost</p>
            <p class="text-2xl font-bold text-gray-800 dark:text-white">KES {{ number_format((float) $stats['today_cost'], 2) }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-4 rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
        <form method="GET" action="{{ route('admin.sms_logs') }}" class="grid grid-cols-1 md:grid-cols-4 gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="🔍 Search recipient / message / ID..."
                   class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm">
            <select name="status" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm">
                <option value="">All Statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <select name="context" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm">
                <option value="">All Contexts</option>
                @foreach($contexts as $ctx)
                    <option value="{{ $ctx }}" @selected(request('context') === $ctx)>{{ $ctx }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    Apply
                </button>
                <a href="{{ route('admin.sms_logs') }}" class="rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                    Clear
                </a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recipient</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Context</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Message</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Error</th>
                        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cost</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($logs as $log)
                        @php
                            $statusClasses = match($log->status) {
                                'delivered' => 'bg-emerald-100 text-emerald-700',
                                'sent', 'queued' => 'bg-blue-100 text-blue-700',
                                'failed', 'rejected' => 'bg-rose-100 text-rose-700',
                                'skipped' => 'bg-amber-100 text-amber-700',
                                default => 'bg-gray-100 text-gray-700',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-3 py-2 text-gray-500 dark:text-gray-400">#{{ $log->id }}</td>
                            <td class="px-3 py-2 text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                {{ $log->created_at->format('d M H:i') }}
                            </td>
                            <td class="px-3 py-2 font-mono text-gray-700 dark:text-gray-300">
                                {{ $log->recipient }}
                            </td>
                            <td class="px-3 py-2">
                                @if($log->context)
                                    <span class="inline-flex rounded-full bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        {{ $log->context }}
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-2">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $statusClasses }}">
                                    {{ $log->status }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-gray-600 dark:text-gray-400 max-w-xs truncate" title="{{ $log->message }}">
                                {{ \Illuminate\Support\Str::limit($log->message, 50) }}
                            </td>
                            <td class="px-3 py-2 text-rose-600 text-xs max-w-xs truncate" title="{{ $log->error }}">
                                {{ $log->error ? \Illuminate\Support\Str::limit($log->error, 40) : '—' }}
                            </td>
                            <td class="px-3 py-2 text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                {{ $log->cost ? 'KES ' . number_format((float) $log->cost, 2) : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-3 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-3xl block mb-2 text-gray-300"></i>
                                No SMS logs yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection