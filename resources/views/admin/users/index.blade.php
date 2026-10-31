{{-- User management. Data: UserController@index as JSON (rows, stats, form options). --}}
@extends('layouts.app')
@section('title', 'User management')

@section('content')
<div x-data="dataTable({ url: '{{ route('users.index') }}', filters: { lane: '', status: '', role: new URLSearchParams(location.search).get('role') || '' } })">
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
      <x-ui.row-menu>
        <x-ui.row-menu-item icon="eye" x-bind:href="row.person.url">View profile</x-ui.row-menu-item>
        <x-ui.row-menu-item icon="envelope" x-bind:href="'mailto:' + row.person.email">Email</x-ui.row-menu-item>
        <x-ui.row-menu-item icon="trash" danger x-show="!row.self"
            @click="sharet.confirm({ title: 'Remove this person?', message: 'They lose access straight away. Their records stay for audit.', tone: 'error', confirmText: 'Remove',
                      details: [['Name', row.person.name], ['Email', row.person.email], ['Lane', row.lane], ['Roles', row.role]] })
              .then(ok => ok && sharet.send('DELETE', '{{ url('users') }}/' + row.id).then(() => remove(row.id)).catch(() => {}))">Remove</x-ui.row-menu-item>
      </x-ui.row-menu>
    </x-slot:actions>
    <x-slot:empty>No people match these filters.</x-slot:empty>
  </x-ui.table>

  @include('admin.users.partials.add-person')
</div>
@endsection
