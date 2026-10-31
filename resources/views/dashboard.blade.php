{{-- Dashboard home. One row of stat cards, charts and a table, each loaded as JSON from DashboardController. --}}
@extends('layouts.app')
@section('title', 'Dashboard')

@php
    $headings = [
        'admin' => ['Admin overview', 'Everyone on Sharet: sign-ups, lanes and the waiting list.'],
        'ops' => ['Operations', 'Waiting list, onboarding and support.'],
        'merchant' => ['Merchant dashboard', 'Accept from any rail. Settle anywhere.'],
        'business' => ['Business dashboard', 'Pay abroad. Get paid from abroad. Settle locally.'],
        'techie' => ['Developer dashboard', 'Build on the Sharet ledger: API keys, sandbox, webhooks.'],
        'individual' => ['Your wallet', 'Send, receive and manage your Sharet balance.'],
    ];
    [$heading, $subheading] = $headings[$lane];
    $staff = in_array($lane, ['admin', 'ops'], true);
@endphp

@section('content')
<x-ui.page-header :title="$heading" :subtitle="$subheading">
  @if ($staff)
    <x-ui.button variant="secondary" icon="document-chart-bar" :href="route('reports.index')">Reports</x-ui.button>
    @if (Route::has('admin.waiting_list'))<x-ui.button icon="queue-list" :href="route('admin.waiting_list')">Waiting list</x-ui.button>@endif
  @elseif ($lane === 'techie')
    <x-ui.button icon="key" :href="route('api-keys.index')">API keys</x-ui.button>
  @else
    <x-ui.button icon="wallet" :href="route('wallet.index')">Open wallet</x-ui.button>
  @endif
</x-ui.page-header>

<div class="grid grid-cols-12 gap-4 md:gap-6" x-data="{ stats: {} }" x-init="stats = (await (await fetch('{{ route('dashboard.data') }}', { headers: { Accept: 'application/json' } })).json()).stats">
  <div class="col-span-12">
    <x-ui.stats :cols="4">
      @if ($staff)
        <x-ui.stat label="People" value="stats.people" delta="stats.people_delta" hint="stats.people_hint" icon="users" tone="violet" />
        <x-ui.stat label="Waiting list" value="stats.waiting" hint="stats.waiting_hint" icon="queue-list" tone="amber" />
        <x-ui.stat label="Active accounts" value="stats.active" hint="stats.active_hint" icon="check-badge" tone="emerald" />
        <x-ui.stat label="Companies" value="stats.companies" hint="stats.companies_hint" icon="building-office-2" tone="sky" />
      @elseif ($lane === 'techie')
        <x-ui.stat label="API keys" value="stats.keys" hint="stats.keys_hint" icon="key" tone="violet" />
        <x-ui.stat label="Sandbox keys" value="stats.sandbox" hint="stats.sandbox_hint" icon="beaker" tone="teal" />
        <x-ui.stat label="Live keys" value="stats.live" hint="stats.live_hint" icon="bolt" tone="amber" />
        <x-ui.stat label="Last used" value="stats.last_used" format="raw" hint="stats.last_used_hint" icon="clock" tone="sky" />
      @else
        <x-ui.stat label="Balance" value="stats.balance" format="money" hint="stats.balance_hint" icon="wallet" tone="lime" />
        <x-ui.stat label="Money in" value="stats.in" format="money" hint="stats.in_hint" icon="arrow-down-left" tone="emerald" />
        <x-ui.stat label="Money out" value="stats.out" format="money" hint="stats.out_hint" icon="arrow-up-right" tone="rose" />
        <x-ui.stat label="Transactions" value="stats.count" hint="stats.count_hint" icon="arrows-right-left" tone="sky" />
      @endif
    </x-ui.stats>
  </div>

  @if ($staff)
    <div class="col-span-12 xl:col-span-8">
      <x-ui.chart :url="route('dashboard.chart', 'signups')" title="Sign-ups" subtitle="New people per day, by lane" :ranges="[7 => '7 days', 30 => '30 days', 90 => '90 days']" />
    </div>
    <div class="col-span-12 xl:col-span-4">
      <x-ui.chart :url="route('dashboard.chart', 'lanes')" title="People by lane" subtitle="What each person signed up for" :height="320" />
    </div>
    <div class="col-span-12 xl:col-span-5">
      <x-ui.chart :url="route('dashboard.chart', 'funnel')" title="Onboarding" subtitle="Where people are in getting set up" :height="260" />
    </div>
    <div class="col-span-12 xl:col-span-7" x-data="dataTable({ url: '{{ route('users.index') }}', perPage: 5 })">
      <x-ui.table title="Newest people" subtitle="Latest sign-ups across every lane" :searchable="false" :columns="[
          ['key' => 'person', 'label' => 'Person', 'type' => 'person'],
          ['key' => 'lane', 'label' => 'Lane'],
          ['key' => 'status', 'label' => 'Status', 'type' => 'pill'],
          ['key' => 'joined', 'label' => 'Joined', 'type' => 'muted'],
      ]">
        <x-slot:buttons><x-ui.button variant="secondary" :href="route('users.index')">View all</x-ui.button></x-slot:buttons>
      </x-ui.table>
    </div>
  @elseif ($lane !== 'techie')
    <div class="col-span-12 xl:col-span-5">
      <x-ui.chart :url="route('dashboard.chart', 'money')" title="Money in and out" subtitle="Last 6 months" :height="300" />
    </div>
    <div class="col-span-12 xl:col-span-7" x-data="dataTable({ url: '{{ route('dashboard.activity') }}', perPage: 5 })">
      <x-ui.table title="Recent activity" :searchable="false" :columns="[
          ['key' => 'type', 'label' => 'Type', 'type' => 'pill'],
          ['key' => 'amount', 'label' => 'Amount', 'type' => 'money', 'sort' => 'amount'],
          ['key' => 'status', 'label' => 'Status', 'type' => 'pill'],
          ['key' => 'date', 'label' => 'Date', 'type' => 'muted', 'sort' => 'date'],
      ]">
        <x-slot:empty>No transactions yet. Your wallet activity will show here.</x-slot:empty>
      </x-ui.table>
    </div>
  @else
    <div class="col-span-12">
      <x-ui.card title="Get started" subtitle="Everything you need to make your first sandbox call">
        <div class="grid gap-4 md:grid-cols-3">
          @foreach ([['key', 'Create a sandbox key', 'api-keys.index'], ['book-open', 'Read the API guide', 'developers'], ['beaker', 'Test M-Pesa in the sandbox', 'integrations.index']] as [$icon, $label, $route])
            @if (Route::has($route))
              <a href="{{ route($route) }}" class="flex items-center gap-3 rounded-xl border border-gray-200 p-4 text-sm font-medium text-gray-800 hover:bg-gray-50 dark:border-gray-800 dark:text-white/90 dark:hover:bg-white/5">
                <x-dynamic-component :component="'heroicon-o-'.$icon" class="h-5 w-5 text-violet-500" /> {{ $label }}
              </a>
            @endif
          @endforeach
        </div>
      </x-ui.card>
    </div>
  @endif
</div>
@endsection
