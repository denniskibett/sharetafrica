{{-- Reports: people per lane. Tabs filter the same JSON as User management (users.index ?lane=). --}}
@extends('layouts.app')
@section('title', 'Reports')

@php $tabs = ['' => 'Everyone'] + \App\Support\People::LANES; @endphp

@section('content')
<div x-data="dataTable({ url: '{{ route('users.index') }}', filters: { lane: new URLSearchParams(location.search).get('lane') || '', status: '', role: '' } })"
     x-effect="history.replaceState(null, '', filters.lane ? '?lane=' + filters.lane : location.pathname)">
  <x-ui.page-header title="Reports" subtitle="How each kind of account is growing, and who is in it.">
    <x-ui.button variant="secondary" icon="arrow-down-tray" x-bind:href="'{{ route('users.export') }}?' + new URLSearchParams({ search, ...filters })">Export CSV</x-ui.button>
  </x-ui.page-header>

  <div class="mb-6 flex gap-1 overflow-x-auto rounded-xl border border-gray-200 bg-white p-1 dark:border-gray-800 dark:bg-white/[0.03]" role="tablist">
    @foreach ($tabs as $key => $label)
      <button type="button" role="tab" @click="filters.lane = '{{ $key }}'" :aria-selected="filters.lane === '{{ $key }}'"
              :class="filters.lane === '{{ $key }}' ? 'bg-primary text-gray-900' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5'"
              class="whitespace-nowrap rounded-lg px-4 py-2 text-sm font-medium">{{ $label }}</button>
    @endforeach
  </div>

  <x-ui.stats :cols="4" class="mb-6">
    <x-ui.stat label="People" value="stats.total" delta="stats.total_delta" hint="stats.total_hint" icon="users" tone="violet" />
    <x-ui.stat label="Active" value="stats.active" hint="stats.active_hint" icon="check-badge" tone="emerald" />
    <x-ui.stat label="Waiting list" value="stats.waiting" hint="stats.waiting_hint" icon="queue-list" tone="amber" />
    <x-ui.stat label="With a company" value="stats.companies" hint="stats.companies_hint" icon="building-office-2" tone="sky" />
  </x-ui.stats>

  <x-ui.chart class="mb-6" :url="route('dashboard.chart', 'signups')" title="Sign-ups" subtitle="New accounts per day" sync="{ lane: filters.lane }"
              :ranges="[7 => '7 days', 30 => '30 days', 90 => '90 days']" />

  <x-ui.table title="People in this report" placeholder="Search name, email or phone…" :columns="[
      ['key' => 'person', 'label' => 'Person', 'type' => 'person', 'sort' => 'name'],
      ['key' => 'lane', 'label' => 'Lane'],
      ['key' => 'company', 'label' => 'Company', 'type' => 'muted'],
      ['key' => 'status', 'label' => 'Status', 'type' => 'pill'],
      ['key' => 'joined', 'label' => 'Joined', 'type' => 'muted', 'sort' => 'joined'],
  ]">
    <x-slot:filters>
      <select x-model="filters.status" aria-label="Status" class="h-10 rounded-lg border border-gray-300 bg-transparent pl-3 pr-9 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
        <option value="">Any status</option>
        <template x-for="(label, key) in extra.statuses" :key="key"><option :value="key" x-text="label"></option></template>
      </select>
    </x-slot:filters>
  </x-ui.table>
</div>
@endsection
