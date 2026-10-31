{{-- User management. Data: UserController@index as JSON (rows, stats, form options). --}}
@extends('layouts.app')
@section('title', 'User management')

@section('content')
<div x-data="dataTable({ url: '{{ route('users.index') }}', filters: { lane: '', status: '', role: '' } })">
  <x-ui.page-header title="User management" subtitle="Everyone with a Sharet account: their lane, roles and where they are in onboarding.">
    <x-ui.button variant="secondary" icon="arrow-down-tray" x-bind:href="'{{ route('users.export') }}?' + new URLSearchParams({ search, ...filters })">Export CSV</x-ui.button>
    <x-ui.button icon="plus" @click="sharet.panel('add-person')">Add person</x-ui.button>
  </x-ui.page-header>

  <x-ui.stats :cols="4" class="mb-6">
    <x-ui.stat label="Total people" value="stats.total" delta="stats.total_delta" hint="stats.total_hint" icon="users" tone="violet" />
    <x-ui.stat label="Active" value="stats.active" hint="stats.active_hint" icon="check-badge" tone="emerald" />
    <x-ui.stat label="Waiting list" value="stats.waiting" hint="stats.waiting_hint" icon="queue-list" tone="amber" />
    <x-ui.stat label="Staff" value="stats.staff" hint="stats.staff_hint" icon="shield-check" tone="sky" />
  </x-ui.stats>

  <x-ui.table title="People" placeholder="Search name, email or phone…" :columns="[
      ['key' => 'person', 'label' => 'Person', 'type' => 'person', 'sort' => 'name'],
      ['key' => 'phone', 'label' => 'Phone', 'type' => 'muted'],
      ['key' => 'lane', 'label' => 'Lane'],
      ['key' => 'role', 'label' => 'Roles', 'class' => 'capitalize'],
      ['key' => 'status', 'label' => 'Status', 'type' => 'pill'],
      ['key' => 'joined', 'label' => 'Joined', 'type' => 'muted', 'sort' => 'joined'],
  ]">
    <x-slot:filters>
      @include('admin.users.partials.filters')
    </x-slot:filters>
    <x-slot:actions>
      <a :href="row.person.url" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-white/5 dark:hover:text-white" title="View"><x-heroicon-o-eye class="h-5 w-5" /></a>
      <button type="button" x-show="!row.self" title="Remove"
              @click="sharet.confirm({ title: 'Remove ' + row.person.name + '?', message: 'They lose access straight away. Their records stay for audit.', tone: 'error', confirmText: 'Remove' })
                .then(ok => ok && sharet.send('DELETE', '{{ url('users') }}/' + row.id).then(() => remove(row.id)).catch(() => {}))"
              class="rounded-lg p-2 text-gray-500 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10"><x-heroicon-o-trash class="h-5 w-5" /></button>
    </x-slot:actions>
    <x-slot:empty>No people match these filters.</x-slot:empty>
  </x-ui.table>

  @include('admin.users.partials.add-person')
</div>
@endsection
