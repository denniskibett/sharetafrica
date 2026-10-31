{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', ($heading ?? 'Dashboard') . ' · Sharet Africa')

@section('content')
<div class="container-fluid px-4 py-6">

    {{-- ============================================================
         HEADER
         ============================================================ --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                {{ now()->format('l, d M Y') }}
            </p>
            <h1 class="mt-1 text-2xl font-semibold text-gray-800 dark:text-white/90">
                {{ $heading ?? 'Dashboard' }}, {{ auth()->user()->first_name ?: auth()->user()->name }}
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                {{ $subheading ?? '' }}
            </p>
        </div>

        @if(auth()->user()->company)
            <div class="rounded-full border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="text-gray-500 dark:text-gray-400">Company:</span>
                <span class="font-medium text-gray-800 dark:text-white/90">
                    {{ auth()->user()->company->name }}
                </span>
            </div>
        @endif
    </div>

    {{-- ============================================================
         CARDS — rendered by the shared partial
         ============================================================ --}}
    @include('partials.card.card-dashboard', ['cardData' => $cardData ?? []])

    {{-- ============================================================
         RECENT ACTIVITY — wallet lanes only
         ============================================================ --}}
    @php
        $role = $cardData['user_role'] ?? 'guest';
    @endphp

    @if(in_array($role, ['individual', 'merchant', 'business']))
        <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Recent activity</h2>
                <a href="{{ route('wallet.index') }}"
                   class="text-sm font-medium text-primary hover:underline">
                    View all
                </a>
            </div>

            @if(($recentActivity ?? collect())->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    No transactions yet. Your wallet activity will appear here.
                </p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b border-gray-200 dark:border-gray-800">
                            <tr class="text-left text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                <th class="py-2">Type</th>
                                <th class="py-2">Amount</th>
                                <th class="py-2">Status</th>
                                <th class="py-2">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($recentActivity as $tx)
                                <tr>
                                    <td class="py-3 capitalize">{{ $tx->type }}</td>
                                    <td class="py-3 font-medium">
                                        {{ $tx->amount_float < 0 ? '-' : '+' }}
                                        KES {{ number_format(abs($tx->amount_float), 2) }}
                                    </td>
                                    <td class="py-3">
                                        <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-600">
                                            {{ $tx->confirmed ? 'Completed' : 'Pending' }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-gray-500 dark:text-gray-400">
                                        {{ $tx->created_at->format('d M Y, H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif

    {{-- ============================================================
         DEVELOPER — recent API activity panel
         ============================================================ --}}
    @if($role === 'techie')
        <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Recent API activity</h2>
                <a href="#" class="text-sm font-medium text-primary hover:underline">View logs</a>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Once you make your first API call, recent requests and errors will appear here.
            </p>
        </div>
    @endif

    {{-- ============================================================
         ADMIN — waiting list + lane breakdown
         ============================================================ --}}
    @if($role === 'admin')
        <div class="mt-6 grid gap-6 lg:grid-cols-2">

            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Waiting list</h2>
                    <a href="{{ route('admin.waiting_list') }}"
                       class="text-sm font-medium text-primary hover:underline">
                        Manage
                    </a>
                </div>

                @php
                    $waitingUsers = \App\Models\User::where('onboarding_status', 'waiting_list')
                        ->latest()
                        ->take(5)
                        ->get();
                @endphp

                @if($waitingUsers->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400">No users waiting.</p>
                @else
                    <ul class="space-y-3">
                        @foreach($waitingUsers as $u)
                            <li class="flex items-center justify-between rounded-lg border border-gray-100 p-3 dark:border-gray-800">
                                <div>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white/90">
                                        {{ $u->name }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $u->email }}</p>
                                </div>
                                <form method="POST" action="{{ route('admin.waiting_list.invite', $u) }}">
                                    @csrf
                                    <button type="submit"
                                            class="rounded-lg bg-primary px-3 py-1 text-xs font-medium text-white hover:brightness-95">
                                        Invite
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">By lane</h2>
                <ul class="space-y-3 text-sm">
                    <li class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Individuals</span>
                        <span class="font-medium">{{ $cardData['individual_count'] ?? 0 }}</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Merchants</span>
                        <span class="font-medium">{{ $cardData['merchant_count'] ?? 0 }}</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Businesses</span>
                        <span class="font-medium">{{ $cardData['business_count'] ?? 0 }}</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Techies</span>
                        <span class="font-medium">{{ $cardData['techie_count'] ?? 0 }}</span>
                    </li>
                </ul>
            </div>

        </div>
    @endif

    {{-- ============================================================
         SUPPORT / OPS — waiting list + rails
         ============================================================ --}}
    @if(in_array($role, ['support', 'operations']))
        <div class="mt-6 grid gap-6 lg:grid-cols-2">

            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Waiting list</h2>
                    <a href="{{ route('admin.waiting_list') }}"
                       class="text-sm font-medium text-primary hover:underline">
                        Manage
                    </a>
                </div>
                <p class="text-3xl font-bold text-gray-800 dark:text-white/90">
                    {{ $cardData['waiting_list_count'] ?? 0 }}
                </p>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    {{ $cardData['invited_count'] ?? 0 }} invited ·
                    {{ $cardData['in_progress_count'] ?? 0 }} in progress
                </p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Rails status</h2>
                <ul class="space-y-3 text-sm">
                    <li class="flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-green-500"></span>
                            <span class="text-gray-500 dark:text-gray-400">Live</span>
                        </span>
                        <span class="font-medium">{{ $cardData['rails_live'] ?? 0 }}</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-yellow-500"></span>
                            <span class="text-gray-500 dark:text-gray-400">Degraded</span>
                        </span>
                        <span class="font-medium">{{ $cardData['rails_degraded'] ?? 0 }}</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-red-500"></span>
                            <span class="text-gray-500 dark:text-gray-400">Down</span>
                        </span>
                        <span class="font-medium">{{ $cardData['rails_down'] ?? 0 }}</span>
                    </li>
                </ul>
            </div>

        </div>
    @endif

    {{-- ============================================================
         WAITING LIST — nothing else on the page
         ============================================================ --}}
    @if($role === 'waiting_list')
        <div class="mt-6 rounded-2xl border border-yellow-200 bg-yellow-50 p-6 dark:border-yellow-900 dark:bg-yellow-900/20">
            <h2 class="text-lg font-semibold text-yellow-800 dark:text-yellow-300">
                You are on the waiting list
            </h2>
            <p class="mt-2 text-sm text-yellow-700 dark:text-yellow-400">
                We will notify you as soon as your Sharet account is ready for onboarding. In the meantime, feel free to read our <a href="{{ route('home') }}" class="underline">homepage</a> or reach out on the <a href="{{ route('contact') }}" class="underline">contact page</a>.
            </p>
        </div>
    @endif

</div>
@endsection