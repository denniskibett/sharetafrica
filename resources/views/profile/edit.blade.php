{{-- Account settings: personal details, verification (M-Pesa grade → bank grade), security, close account.
     Data: GET /profile/data; each tab saves in the background. --}}
@extends('layouts.app')
@section('title', 'Account settings')

@php
    // [label, icon, colour by meaning]: you blue, verification Sharet lime, security ink, closing red
    $tabs = ['personal' => ['Personal details', 'user', 'bg-app-send/15 text-app-send'], 'verification' => ['Verification', 'identification', 'bg-app-lime text-[#050507]'], 'security' => ['Password & security', 'lock-closed', 'bg-gray-900 text-white dark:bg-white/10'], 'close' => ['Close account', 'x-circle', 'bg-app-out/15 text-app-out']];
@endphp

@section('content')
<div x-data="profile('{{ route('profile.data') }}')">
  <div x-data="{ tab: ['personal', 'verification', 'security', 'close'].includes(location.hash.slice(1)) ? location.hash.slice(1) : 'personal' }"
       x-effect="history.replaceState(null, '', '#' + tab)">
    <x-ui.page-header title="Account settings" subtitle="Keep your details current. Verified details unlock higher limits and more services." />
    @if ($kycReturn ?? null)
      <div class="mb-6 flex flex-col gap-3 rounded-2xl border border-lime-200 bg-lime-50 p-4 text-sm text-lime-900 sm:flex-row sm:items-center sm:justify-between dark:border-lime-500/30 dark:bg-lime-500/10 dark:text-lime-100">
        <p>Verify your name, M-PESA number and ID here, then go back to finish your payment.</p>
        <a href="{{ url($kycReturn) }}" class="inline-flex h-10 shrink-0 items-center justify-center rounded-full bg-gray-900 px-4 font-semibold text-white hover:bg-gray-800">Back to my payment</a>
      </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[240px_minmax(0,1fr)]">
      <nav class="ui-card flex gap-1 overflow-x-auto rounded-2xl border border-gray-200 bg-white p-2 dark:border-gray-800 dark:bg-white/[0.03] lg:h-fit lg:flex-col" aria-label="Settings">
        @foreach ($tabs as $key => [$label, $icon, $tone])
          <button type="button" @click="tab = '{{ $key }}'" :aria-current="tab === '{{ $key }}' && 'page'"
                  :class="tab === '{{ $key }}' ? 'bg-gray-100 text-gray-900 dark:bg-white/[0.06]' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5'"
                  class="flex items-center gap-3 whitespace-nowrap rounded-full px-2 py-2 pr-4 text-left text-sm font-semibold lg:rounded-2xl">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $tone }}"><x-dynamic-component :component="'heroicon-o-'.$icon" class="h-5 w-5" /></span> {{ $label }}
          </button>
        @endforeach
      </nav>

      <div x-show="loading" class="ui-card rounded-2xl bg-white p-10 text-center text-sm text-gray-500 dark:bg-white/[0.03]">Loading your details…</div>
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
