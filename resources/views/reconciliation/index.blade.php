{{-- Reconciliation days: one row per rail per day. Data: ReconciliationController@index as JSON; "Run" imports and matches in place. --}}
@extends('layouts.app')
@section('title', 'Reconciliation')

@section('content')
<div x-data="dataTable({ url: '{{ route('ops.reconciliation.index') }}', sort: '-day' })">
  <x-ui.page-header title="Reconciliation" subtitle="Every line on the provider's statement must match the ledger, and every ledger entry must be on the statement. Each day is signed off by two people." />

  <x-ui.stats :cols="4" class="mb-6">
    <x-ui.stat label="Open breaks" value="stats.open_breaks" icon="exclamation-triangle" tone="rose" />
    <x-ui.stat label="Waiting for checker" value="stats.waiting" icon="user-group" tone="amber" />
    <x-ui.stat label="Days signed off" value="stats.signed" icon="check-badge" tone="emerald" />
    <x-ui.stat label="Days not signed off" value="stats.unsigned" icon="calendar-days" tone="sky" />
  </x-ui.stats>

  <x-ui.table title="Days" :searchable="false" :columns="[
      ['key' => 'day', 'label' => 'Day', 'sort' => 'day'],
      ['key' => 'rail', 'label' => 'Rail', 'type' => 'muted'],
      ['key' => 'lines', 'label' => 'Lines', 'type' => 'number'],
      ['key' => 'breaks', 'label' => 'Open breaks', 'type' => 'number'],
      ['key' => 'money_in', 'label' => 'In', 'type' => 'code'],
      ['key' => 'money_out', 'label' => 'Out', 'type' => 'code'],
      ['key' => 'pill', 'label' => 'Status', 'type' => 'pill'],
  ]">
    <x-slot:buttons>
      @include('reconciliation.partials.run', ['rail' => $rail])
    </x-slot:buttons>
    <x-slot:actions>
      <x-ui.row-menu>
        <x-ui.row-menu-item icon="eye" x-bind:href="row.url">Open</x-ui.row-menu-item>
      </x-ui.row-menu>
    </x-slot:actions>
    <x-slot:empty>No days reconciled yet. Run a day to import its statement.</x-slot:empty>
  </x-ui.table>
</div>
@endsection
