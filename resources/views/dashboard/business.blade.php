@extends('layouts.app')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">
            {{ $cardData['company_name'] ?? 'Business' }} Dashboard
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Pay abroad. Get paid from abroad. Settle locally.</p>
    </div>

    @include('partials.card.card-dashboard', ['cardData' => $cardData])

    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Trade tools</h2>
        <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
            <a href="#" class="rounded-lg border border-gray-200 p-4 text-center hover:bg-primary-10 dark:border-gray-700">
                <span class="block text-sm font-medium">Invoices</span>
            </a>
            <a href="#" class="rounded-lg border border-gray-200 p-4 text-center hover:bg-primary-10 dark:border-gray-700">
                <span class="block text-sm font-medium">Suppliers</span>
            </a>
            <a href="#" class="rounded-lg border border-gray-200 p-4 text-center hover:bg-primary-10 dark:border-gray-700">
                <span class="block text-sm font-medium">Trade Finance</span>
            </a>
            <a href="#" class="rounded-lg border border-gray-200 p-4 text-center hover:bg-primary-10 dark:border-gray-700">
                <span class="block text-sm font-medium">Corridors</span>
            </a>
        </div>
    </div>
@endsection