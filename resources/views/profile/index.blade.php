{{-- Profile overview. Data: GET /profile/data (ProfileController@getUserData). --}}
@extends('layouts.app')
@section('title', 'Profile')

@section('content')
<div x-data="profile('{{ route('profile.data') }}')">
  <x-ui.page-header title="Profile" subtitle="Your Sharet account at a glance." />

  @include('profile.partials.summary-card')

  @php $company = auth()->user()->company; @endphp
  <x-ui.card class="mb-6" :title="$company ? $company->name : 'Start a business'"
             :subtitle="$company ? ($company->verified_at ? 'Verified business. You are its account holder.' : 'Your business. Finish its details and documents so our team can verify it.') : 'Run a shop or a company? Open a business account from this one: its own Sharet ID, payments and settlements. Your personal account stays as it is.'">
    <x-slot:actions>
      <x-ui.button :variant="$company ? 'secondary' : 'primary'" icon="building-storefront" href="{{ route('settings.company') }}">{{ $company ? 'Business settings' : 'Start a business' }}</x-ui.button>
    </x-slot:actions>
  </x-ui.card>

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
