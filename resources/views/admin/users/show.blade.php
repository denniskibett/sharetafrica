{{-- One person. Data: UserController@show as JSON; wallet activity from users.activity. --}}
@extends('layouts.app')
@section('title', $user->name)

@section('content')
<div x-data="{ d: { person: {}, level: { basic: {}, standard: {} }, stats: {}, details: [], documents: [], timeline: [], roles: [], all_roles: [] }, loading: true,
               async load() { this.d = await (await fetch('{{ route('users.show', $user) }}', { headers: { Accept: 'application/json' } })).json(); this.loading = false } }"
     x-init="load()">
  <x-ui.page-header title="Person" subtitle="Account, verification and activity.">
    <x-ui.button variant="secondary" icon="arrow-left" href="{{ route('users.index') }}">All people</x-ui.button>
  </x-ui.page-header>

  @include('admin.users.partials.summary')

  <x-ui.stats :cols="4" class="mb-6">
    <x-ui.stat label="Account level" value="d.level.label" format="raw" icon="shield-check" tone="lime" />
    <x-ui.stat label="Wallet balance" value="d.stats.balance" format="money" icon="wallet" tone="emerald" />
    <x-ui.stat label="Transactions" value="d.stats.transactions" icon="arrows-right-left" tone="sky" />
    <x-ui.stat label="API keys" value="d.stats.keys" icon="key" tone="violet" />
  </x-ui.stats>

  <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(0,1.6fr)]">
    <div class="space-y-6">
      @include('admin.users.partials.details')
      @include('admin.users.partials.timeline')
      @include('admin.users.partials.footprint')
    </div>
    <div class="space-y-6">
      @include('admin.users.partials.documents')
      <div x-data="dataTable({ url: '{{ route('users.activity', $user) }}', perPage: 5 })">
        <x-ui.table title="Wallet activity" :searchable="false" :columns="[
            ['key' => 'type', 'label' => 'Type', 'type' => 'pill'],
            ['key' => 'amount', 'label' => 'Amount', 'type' => 'money', 'sort' => 'amount'],
            ['key' => 'status', 'label' => 'Status', 'type' => 'pill'],
            ['key' => 'date', 'label' => 'Date', 'type' => 'muted', 'sort' => 'date'],
        ]">
          <x-slot:empty>No wallet activity yet.</x-slot:empty>
        </x-ui.table>
      </div>
    </div>
  </div>

  @include('admin.users.partials.roles')
</div>
@endsection
