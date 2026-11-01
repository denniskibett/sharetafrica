{{-- Account settings: personal details, verification (M-Pesa grade → bank grade), security, close account.
     Data: GET /profile/data; each tab saves in the background. --}}
@extends('layouts.app')
@section('title', 'Account settings')

@php
    $tabs = ['personal' => ['Personal details', 'user'], 'verification' => ['Verification', 'identification'], 'security' => ['Password & security', 'lock-closed'], 'close' => ['Close account', 'x-circle']];
@endphp

@section('content')
<div x-data="profile('{{ route('profile.data') }}')">
  <div x-data="{ tab: ['personal', 'verification', 'security', 'close'].includes(location.hash.slice(1)) ? location.hash.slice(1) : 'personal' }"
       x-effect="history.replaceState(null, '', '#' + tab)">
    <x-ui.page-header title="Account settings" subtitle="Keep your details current. Verified details unlock higher limits and more services." />

    <div class="grid gap-6 lg:grid-cols-[240px_minmax(0,1fr)]">
      <nav class="flex gap-1 overflow-x-auto rounded-2xl border border-gray-200 bg-white p-2 dark:border-gray-800 dark:bg-white/[0.03] lg:h-fit lg:flex-col" aria-label="Settings">
        @foreach ($tabs as $key => [$label, $icon])
          <button type="button" @click="tab = '{{ $key }}'" :aria-current="tab === '{{ $key }}' && 'page'"
                  :class="tab === '{{ $key }}' ? 'bg-primary text-gray-900' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5'"
                  class="flex items-center gap-3 whitespace-nowrap rounded-lg px-3 py-2.5 text-left text-sm font-medium">
            <x-dynamic-component :component="'heroicon-o-'.$icon" class="h-5 w-5" /> {{ $label }}
          </button>
        @endforeach
      </nav>

      <div x-show="loading" class="rounded-2xl border border-gray-200 p-10 text-center text-sm text-gray-500 dark:border-gray-800">Loading your details…</div>
      <div x-show="!loading" x-cloak>
        <div x-show="tab === 'personal'">@include('profile.partials.personal-form')</div>
        <div x-show="tab === 'verification'">@include('profile.partials.verification-form')</div>
        <div x-show="tab === 'security'">@include('profile.partials.security-form')</div>
        <div x-show="tab === 'close'">@include('profile.partials.close-account')</div>
      </div>
    </div>
  </div>
</div>
@endsection
