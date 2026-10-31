{{-- Admin: people waiting for access. Data: WaitingListController@index as JSON. Inviting emails them a link to onboarding. --}}
@extends('layouts.app')
@section('title', 'Waiting list')

@section('content')
<div x-data="dataTable({ url: '{{ route('admin.waiting_list') }}', filters: { lane: '' } })">
  <x-ui.page-header title="Waiting list" subtitle="People waiting for access. Inviting someone emails them a link to choose their lane and finish setting up." />

  <x-ui.stats :cols="4" class="mb-6">
    <x-ui.stat label="Waiting" value="stats.waiting" hint="stats.waiting_hint" icon="queue-list" tone="amber" />
    <x-ui.stat label="New this week" value="stats.week" hint="stats.week_hint" icon="sparkles" tone="violet" />
    <x-ui.stat label="Invited" value="stats.invited" hint="stats.invited_hint" icon="envelope" tone="sky" />
    <x-ui.stat label="Now active" value="stats.converted" hint="stats.converted_hint" icon="check-badge" tone="emerald" />
  </x-ui.stats>

  <x-ui.table title="Waiting" placeholder="Search name, email or phone…" :columns="[
      ['key' => 'person', 'label' => 'Person', 'type' => 'person', 'sort' => 'name'],
      ['key' => 'phone', 'label' => 'Phone', 'type' => 'muted'],
      ['key' => 'lane', 'label' => 'Interested in'],
      ['key' => 'source', 'label' => 'Came from', 'type' => 'muted'],
      ['key' => 'status', 'label' => 'Status', 'type' => 'pill'],
      ['key' => 'joined', 'label' => 'Joined', 'type' => 'muted', 'sort' => 'joined'],
  ]">
    <x-slot:filters>
      <select x-model="filters.lane" aria-label="Lane" class="h-10 rounded-lg border border-gray-300 bg-transparent pl-3 pr-9 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
        <option value="">All lanes</option>
        <template x-for="(label, key) in extra.lanes" :key="key"><option :value="key" x-text="label"></option></template>
      </select>
    </x-slot:filters>
    <x-slot:actions>
      <x-ui.button class="!h-9" icon="paper-airplane"
          @click="sharet.confirm({ title: 'Invite ' + row.person.name + '?', message: 'We email ' + row.person.email + ' a link to choose their lane and finish setting up.', confirmText: 'Send invite', tone: 'info' })
            .then(ok => ok && sharet.send('POST', '{{ url('admin/waiting-list') }}/' + row.id + '/invite').then(() => { remove(row.id); stats.waiting--; stats.invited++ }).catch(() => {}))">Invite</x-ui.button>
    </x-slot:actions>
    <x-slot:empty>Nobody is waiting right now.</x-slot:empty>
  </x-ui.table>
</div>
@endsection
