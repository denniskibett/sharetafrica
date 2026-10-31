{{-- Ledger: every account with its balance, and the trial balance. Data: LedgerController@index as JSON (no reload). --}}
@extends('layouts.app')
@section('title', 'Ledger')

@section('content')
<div x-data="dataTable({ url: '{{ route('ledger.index') }}', sort: 'code', perPage: 50, filters: { book: '', type: '' } })">
  <x-ui.page-header title="Ledger" subtitle="Every account in Sharet's books. Money only moves through balanced, insert-only journals; the hourly check proves it." />

  <x-ui.stats :cols="4" class="mb-6">
    <x-ui.stat label="Total debits" value="stats.debits" format="raw" icon="arrow-down-left" tone="sky" />
    <x-ui.stat label="Total credits" value="stats.credits" format="raw" icon="arrow-up-right" tone="violet" />
    <x-ui.stat label="Trial balance" value="stats.balanced" format="raw" icon="scale" tone="emerald" />
    <x-ui.stat label="Last ledger check" value="stats.check" format="raw" icon="shield-check" tone="indigo" />
  </x-ui.stats>

  <x-ui.table title="Accounts" placeholder="Search code, name or owner…" :columns="[
      ['key' => 'account', 'label' => 'Account', 'type' => 'link', 'sort' => 'code'],
      ['key' => 'kind', 'label' => 'Type', 'type' => 'pill'],
      ['key' => 'book', 'label' => 'Book', 'type' => 'muted'],
      ['key' => 'balance', 'label' => 'Balance (KES)', 'type' => 'code'],
      ['key' => 'held', 'label' => 'Held', 'type' => 'muted'],
      ['key' => 'available', 'label' => 'Available', 'type' => 'code'],
  ]">
    <x-slot:filters>
      <select x-model="filters.book" aria-label="Book" class="h-10 rounded-lg border border-gray-300 bg-transparent pl-3 pr-9 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
        <option value="">All books</option><option value="rail">Real money (rail)</option><option value="customer">Owed to customers</option><option value="gl">Sharet's books</option>
      </select>
      <select x-model="filters.type" aria-label="Type" class="h-10 rounded-lg border border-gray-300 bg-transparent pl-3 pr-9 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
        <option value="">All types</option><option value="asset">Assets</option><option value="liability">Liabilities</option><option value="equity">Equity</option><option value="income">Income</option><option value="expense">Expenses</option>
      </select>
    </x-slot:filters>
    <x-slot:empty>No accounts yet. Run <code>php artisan db:seed --class=LedgerAccountSeeder</code>.</x-slot:empty>
  </x-ui.table>
</div>
@endsection
