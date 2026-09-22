@extends('layouts.app')

@section('title', 'Waiting List')

@php
    $users = $users ?? collect([]);
    $totalWaiting = method_exists($users, 'total') ? $users->total() : $users->count();
@endphp

@section('content')
<div class="container mx-auto px-4 py-6 max-w-screen-2xl">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- ============================================================ --}}
    {{-- HEADER --}}
    {{-- ============================================================ --}}
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                <i class="fas fa-hourglass-half text-amber-600 dark:text-amber-400"></i>
                Waiting List
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Users waiting to be invited for onboarding. Click <strong>Invite</strong> to send an SMS and move them into the onboarding flow.
            </p>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- FLASH MESSAGES --}}
    {{-- ============================================================ --}}
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 px-4 py-3 text-sm text-emerald-800 dark:text-emerald-200 flex items-center gap-2">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 px-4 py-3 text-sm text-rose-800 dark:text-rose-200 flex items-center gap-2">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    {{-- ============================================================ --}}
    {{-- STATS --}}
    {{-- ============================================================ --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-5 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-amber-100 dark:bg-amber-900/20 rounded-full -mr-8 -mt-8 opacity-50"></div>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Waiting</p>
                    <p class="text-2xl font-bold text-amber-600">{{ $totalWaiting }}</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                    <i class="fas fa-hourglass-half text-amber-600 dark:text-amber-400 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- TABLE --}}
    {{-- ============================================================ --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm">
        <div class="w-full overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">User</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Phone</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Intent</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Joined</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                #{{ $user->id }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-sm font-bold">
                                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                    </div>
                                    <div class="text-sm font-medium text-gray-800 dark:text-white">
                                        {{ $user->name }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                {{ $user->email }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                                {{ $user->phone ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full bg-indigo-100 dark:bg-indigo-900/30 px-2.5 py-0.5 text-xs font-medium text-indigo-700 dark:text-indigo-300">
                                    {{ ucfirst($user->intent ?? 'undecided') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                {{ $user->created_at?->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('admin.waiting_list.invite', $user->id) }}" class="inline">
                                    @csrf
                                    <button type="submit"
                                            onclick="return confirm('Invite {{ addslashes($user->name) }}? This will send them an SMS and move them into onboarding.');"
                                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 transition">
                                        <i class="fas fa-paper-plane"></i> Invite
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-inbox text-3xl block mb-2 text-gray-300 dark:text-gray-600"></i>
                                No users on the waiting list.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="flex flex-wrap items-center justify-between gap-4 p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/30">
            <div class="text-sm text-gray-600 dark:text-gray-400">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection