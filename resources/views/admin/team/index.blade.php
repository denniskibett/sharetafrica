{{-- Website › Team. The people shown on sharet.africa/company#team. Data: TeamController@index as JSON. --}}
@extends('layouts.app')
@section('title', 'Team')

@section('content')
<div x-data="dataTable({ url: '{{ route('admin.team.index') }}', sort: 'sort' })">
  <x-ui.page-header title="Team" subtitle="Who appears on sharet.africa/company. Add a clear head-and-shoulders photo for each person; order sets who shows first.">
    <x-ui.button variant="secondary" icon="arrow-top-right-on-square" href="{{ route('company') }}#team" target="_blank">View on website</x-ui.button>
    <x-ui.button icon="plus" @click="sharet.panel('member', { published: true })">Add person</x-ui.button>
  </x-ui.page-header>

  <x-ui.stats :cols="3" class="mb-6">
    <x-ui.stat label="Team members" value="stats.total" hint="stats.total_hint" icon="user-group" tone="violet" />
    <x-ui.stat label="On the website" value="stats.published" hint="stats.published_hint" icon="globe-alt" tone="emerald" />
    <x-ui.stat label="With a photo" value="stats.photos" hint="stats.photos_hint" icon="camera" tone="sky" />
  </x-ui.stats>

  <x-ui.table title="People" placeholder="Search name or role…" :columns="[
      ['key' => 'person', 'label' => 'Person', 'type' => 'person', 'sort' => 'name'],
      ['key' => 'sort', 'label' => 'Order', 'type' => 'number', 'sort' => 'sort'],
      ['key' => 'status', 'label' => 'Website', 'type' => 'pill'],
  ]">
    <x-slot:actions>
      <x-ui.row-menu>
        <x-ui.row-menu-item icon="pencil-square" @click="sharet.panel('member', row)">Edit</x-ui.row-menu-item>
        <x-ui.row-menu-item icon="trash" danger
            @click="sharet.confirm({ title: 'Remove from the team?', message: 'They will no longer show on the website.', tone: 'error', confirmText: 'Remove', details: [['Name', row.name], ['Role', row.role]] })
              .then(ok => ok && sharet.send('DELETE', '{{ url('admin/team') }}/' + row.id).then(() => load()).catch(() => {}))">Remove</x-ui.row-menu-item>
      </x-ui.row-menu>
    </x-slot:actions>
    <x-slot:empty>No one on the team yet.</x-slot:empty>
  </x-ui.table>

  @include('admin.team.partials.form')
</div>
@endsection
