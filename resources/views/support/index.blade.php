{{-- Support desk · cases queue (design: DashSupport). JSON table sorted by SLA left; take, work and close cases in a
     panel, no reloads. Support can't move money from here. --}}
@extends('layouts.app')
@section('title', 'Support desk')

@section('content')
<div x-data="dataTable({ url: '{{ route('ops.support.index') }}', sort: 'sla', perPage: 25, filters: { status: 'open', mine: '' } })"
     x-init="window.support = {
       async act(url, body) {
         try { const r = await sharet.send('POST', url, body || {}); window.dispatchEvent(new CustomEvent('table-reload')); return r; }
         catch (e) { sharet.toast(e.errors ? Object.values(e.errors)[0][0] : (e.body?.message || 'Could not save'), 'error'); throw e; }
       },
     }">
  <x-ui.page-header title="Support desk" subtitle="Cases sorted by SLA time left. Every action is recorded with your name.">
    <x-ui.button icon="arrow-right-circle" @click="support.act('{{ route('ops.support.next') }}').then(r => r.case && sharet.panel('case', r.case))">Take next case</x-ui.button>
  </x-ui.page-header>

  <x-ui.stats :cols="3" class="mb-6">
    <x-ui.stat label="Open cases" value="stats.open" icon="inbox" tone="sky" />
    <x-ui.stat label="Breached SLA" value="stats.breached" icon="exclamation-triangle" tone="rose" />
    <x-ui.stat label="Unassigned" value="stats.unassigned" icon="user-plus" tone="amber" />
  </x-ui.stats>

  <x-ui.table title="Cases queue" placeholder="Search case number or summary…" :columns="[
      ['key' => 'number', 'label' => 'Case', 'type' => 'code'],
      ['key' => 'customer', 'label' => 'Customer', 'type' => 'person'],
      ['key' => 'reason', 'label' => 'Reason code', 'type' => 'code'],
      ['key' => 'sla_pill', 'label' => 'SLA left', 'type' => 'pill', 'sort' => 'sla'],
      ['key' => 'owner', 'label' => 'Owner', 'type' => 'muted'],
      ['key' => 'pill', 'label' => 'Status', 'type' => 'pill'],
  ]">
    <x-slot:filters>
      <select x-model="filters.status" aria-label="Show" class="h-10 rounded-lg border border-gray-300 bg-transparent pl-3 pr-9 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
        <option value="open">Open</option><option value="closed">Closed</option><option value="all">All</option>
      </select>
      <label class="flex h-10 items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
        <input type="checkbox" @change="filters.mine = $event.target.checked ? '1' : ''" class="h-4 w-4 rounded border-gray-300"> My cases only
      </label>
    </x-slot:filters>
    <x-slot:actions>
      <x-ui.row-menu>
        <x-ui.row-menu-item icon="eye" @click="sharet.panel('case', row)">Open</x-ui.row-menu-item>
        <x-ui.row-menu-item icon="user-plus" x-show="row.is_open && !row.mine" @click="support.act('{{ url('ops/support') }}/' + row.id + '/assign')">Assign to me</x-ui.row-menu-item>
      </x-ui.row-menu>
    </x-slot:actions>
    <x-slot:empty>No cases here.</x-slot:empty>
  </x-ui.table>

  @include('support.partials.case')
</div>
@endsection
