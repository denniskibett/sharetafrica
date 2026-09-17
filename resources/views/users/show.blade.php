@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Profile Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">{{ $user->name }}</h1>
            <p class="text-gray-600 mt-1">{{ ucfirst($user->role) }} Profile</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('loans.create', $user->id) }}" 
               class="flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-500 text-white rounded-lg shadow hover:from-blue-700 hover:to-blue-600 transition-all">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                New Loan
            </a>
        </div>
    </div>

    <!-- User Details Card -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6 border border-gray-100">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Name</p>
                    <p class="font-medium">{{ $user->name }}</p>
                </div>
            </div>
            
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-green-100 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Email</p>
                    <p class="font-medium">{{ $user->email }}</p>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Phone</p>
                    <p class="font-medium">{{ $user->phone ?? 'N/A' }}</p>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <div class="p-2 bg-pink-100 rounded-lg">
                    <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Role</p>
                    <p class="font-medium">{{ ucfirst($user->role) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Total Loans</p>
                    <p class="text-2xl font-bold">{{ $user->loans->count() }}</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Total Loaned</p>
                    <p class="text-2xl font-bold">KES {{ number_format($user->loans->sum('amount')) }}</p>
                </div>
                <div class="bg-green-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Total Disbursed</p>
                    <p class="text-2xl font-bold">KES {{ number_format($user->disbursements->sum('amount')) }}</p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Total Repaid</p>
                    <p class="text-2xl font-bold">KES {{ number_format($user->repayments->sum('amount')) }}</p>
                </div>
                <div class="bg-purple-100 p-3 rounded-lg">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Loans Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-xl font-semibold text-gray-800">Loan History</h2>
        </div>
        
        @if($user->loans->count())
            <div class="divide-y divide-gray-100">
                @foreach($user->loans as $loan)
                @php
                    $borrowDate = $loan->borrow_date;
                    $period = $loan->loanType->period;
                    $dueDate = \Carbon\Carbon::parse($borrowDate)->addDays($period);
                @endphp
                <a href="{{ route('users.loans.show', ['user' => $user->id, 'loan' => $loan->id]) }}" 
                   class="block p-6 hover:bg-gray-50 transition-colors">
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="flex items-center space-x-3 mb-2">
                                <span class="font-medium">#{{ str_pad($loan->id, 5, '0', STR_PAD_LEFT) }}</span>
                                <span class="px-2 py-1 text-xs rounded-full 
                                    @if($loan->status === 'approved') bg-green-100 text-green-800
                                    @elseif($loan->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($loan->status === 'rejected') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($loan->status) }}
                                </span>
                            </div>
                            <p class="text-gray-600 text-sm">
                                {{ $borrowDate->format('M d, Y') }} - {{ $dueDate->format('M d, Y') }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-semibold">KES {{ number_format($loan->amount) }}</p>
                            <p class="text-sm text-gray-500">{{ $loan->loanType->name }}</p>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        @else
            <div class="p-6 text-center text-gray-500">
                No loans found. Click "New Loan" to create one.
            </div>
        @endif
    </div>
</div>
@endsection