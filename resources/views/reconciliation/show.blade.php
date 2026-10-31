{{-- One day: statement lines and how each matched; fix, park, prepare and sign off in place (no reloads). --}}
@extends('layouts.app')
@section('title', 'Reconciliation · '.$day['day'])

@section('content')
<div x-data="dataTable({ url: '{{ route('ops.reconciliation.show', $day['id']) }}', sort: 'time', perPage: 25, filters: { status: '' } })">
  <x-ui.page-header :title="$day['day']" :subtitle="'Reconciliation · '.$day['rail']" />
  <p class="-mt-4 mb-6 text-sm"><a href="{{ route('ops.reconciliation.index') }}" class="text-gray-500 hover:underline">← All days</a></p>

  <x-ui.stats :cols="4" class="mb-6">
    <x-ui.stat label="Statement lines" value="stats.lines" icon="document-text" tone="sky" />
    <x-ui.stat label="Matched" value="stats.matched" icon="check-circle" tone="emerald" />
    <x-ui.stat label="Open breaks" value="stats.breaks" icon="exclamation-triangle" tone="rose" />
    <x-ui.stat label="Booked, not on statement" value="stats.not_on_statement" icon="clock" tone="amber" />
  </x-ui.stats>

  @include('reconciliation.partials.signoff', ['dayId' => $day['id']])

  <x-ui.table title="Statement lines" placeholder="Search reference, receipt or number…" :columns="[
      ['key' => 'time', 'label' => 'Time', 'type' => 'muted', 'sort' => 'time'],
      ['key' => 'receipt', 'label' => 'Receipt', 'type' => 'code'],
      ['key' => 'direction', 'label' => 'Way', 'type' => 'muted'],
      ['key' => 'amount', 'label' => 'Amount', 'type' => 'code'],
      ['key' => 'party', 'label' => 'Number / account', 'type' => 'muted'],
      ['key' => 'pill', 'label' => 'Match', 'type' => 'pill'],
      ['key' => 'note', 'label' => 'Note', 'type' => 'muted'],
  ]">
    <x-slot:filters>
      <select x-model="filters.status" aria-label="Show" class="h-10 rounded-lg border border-gray-300 bg-transparent pl-3 pr-9 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
        <option value="">All lines</option><option value="open">Open breaks</option><option value="done">Matched or resolved</option>
      </select>
    </x-slot:filters>
    <x-slot:actions>
      <x-ui.row-menu>
        <x-ui.row-menu-item icon="check" x-show="row.can_fix" @click="recon.act('{{ url('ops/reconciliation/lines') }}/' + row.id + '/fix', 'Book this line from the statement? The order moves on as if the callback had arrived.')">Book from statement</x-ui.row-menu-item>
        <x-ui.row-menu-item icon="archive-box" x-show="row.can_park" @click="recon.act('{{ url('ops/reconciliation/lines') }}/' + row.id + '/park', 'Park this money in suspense until it is identified?')">Park in suspense</x-ui.row-menu-item>
      </x-ui.row-menu>
    </x-slot:actions>
    <x-slot:empty>No lines for this day.</x-slot:empty>
  </x-ui.table>
</div>

<script>
  window.recon = {
    async act(url, question) {
      if (!(await sharet.confirm({ title: 'Are you sure?', message: question }))) return;
      try { await sharet.send('POST', url); } catch (e) { if (e.errors) sharet.toast(Object.values(e.errors)[0][0], 'error'); }
      window.dispatchEvent(new CustomEvent('table-reload'));
    },
  };
</script>
@endsection
