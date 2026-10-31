{{-- Profile overview. Data: GET /profile/data (ProfileController@getUserData). --}}
@extends('layouts.app')
@section('title', 'Profile')

@section('content')
<div x-data="profile('{{ route('profile.data') }}')">
  <x-ui.page-header title="Profile" subtitle="Your Sharet account at a glance.">
    <x-ui.button icon="pencil-square" href="{{ route('profile.edit') }}">Edit profile</x-ui.button>
  </x-ui.page-header>

  @include('profile.partials.summary-card')

  <x-ui.stats :cols="4" class="mb-6">
    <x-ui.stat label="Account level" value="data.level.label" format="raw" icon="shield-check" tone="lime" />
    <x-ui.stat label="Wallet balance" value="data.wallet.balance" format="money" icon="wallet" tone="emerald" />
    <x-ui.stat label="Member since" value="data.user.joined" format="raw" icon="calendar-days" tone="sky" />
    <x-ui.stat label="Email" value="data.user.email_verified ? 'Verified' : 'Not verified'" format="raw" icon="envelope" tone="violet" />
  </x-ui.stats>

  <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(0,1.4fr)]">
    @include('profile.partials.level-card')

    <div x-data="dataTable({ url: '{{ route('dashboard.activity') }}', perPage: 5 })">
      <x-ui.table title="Recent activity" :searchable="false" :columns="[
          ['key' => 'type', 'label' => 'Type', 'type' => 'pill'],
          ['key' => 'amount', 'label' => 'Amount', 'type' => 'money'],
          ['key' => 'status', 'label' => 'Status', 'type' => 'pill'],
          ['key' => 'date', 'label' => 'Date', 'type' => 'muted'],
      ]">
        <x-slot:empty>No wallet activity yet.</x-slot:empty>
      </x-ui.table>
    </div>
  </div>
</div>
@endsection
