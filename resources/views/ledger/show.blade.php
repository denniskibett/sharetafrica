{{-- One ledger account: balance, holds and every posting. Data: LedgerController@show as JSON; journals open in the slide-over. --}}
@extends('layouts.app')
@section('title', $account['label'])

@section('content')
<div x-data="dataTable({ url: '{{ route('ledger.show', $account['id']) }}', sort: '-date', perPage: 25 })">
  <x-ui.page-header :title="$account['label']" :subtitle="ucfirst($account['type']).' · '.$account['book'].' book · '.$account['currency']" />
  <p class="-mt-4 mb-6 text-sm"><a href="{{ route('ledger.index') }}" class="text-gray-500 hover:underline">← All accounts</a></p>

  <x-ui.stats :cols="4" class="mb-6">
    <x-ui.stat label="Balance" value="stats.balance" format="raw" icon="banknotes" tone="emerald" />
    <x-ui.stat label="Held" value="stats.held" format="raw" icon="lock-closed" tone="amber" />
    <x-ui.stat label="Available" value="stats.available" format="raw" icon="check-circle" tone="sky" />
    <x-ui.stat label="Postings" value="stats.count" icon="list-bullet" tone="violet" />
  </x-ui.stats>

  <x-ui.table title="Statement" :searchable="false" :columns="[
      ['key' => 'date', 'label' => 'Date', 'type' => 'muted', 'sort' => 'date'],
      ['key' => 'journal', 'label' => 'Journal'],
      ['key' => 'memo', 'label' => 'Memo', 'type' => 'muted'],
      ['key' => 'debit', 'label' => 'Debit', 'type' => 'code'],
      ['key' => 'credit', 'label' => 'Credit', 'type' => 'code'],
  ]">
    <x-slot:actions>
      <x-ui.row-menu>
        <x-ui.row-menu-item icon="eye" @click="ledger.openJournal(row.journal_url)">Open journal</x-ui.row-menu-item>
      </x-ui.row-menu>
    </x-slot:actions>
    <x-slot:empty>No postings yet.</x-slot:empty>
  </x-ui.table>

  @include('ledger.partials.journal')
</div>
@endsection
