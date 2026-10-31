{{-- System settings (platform admins). Data: SystemController@index as JSON; saves as multipart (logo uploads). --}}
@extends('layouts.app')
@section('title', 'System')

@php
    $tabs = ['general' => ['General', 'building-office'], 'regional' => ['Region & money', 'globe-europe-africa'], 'brand' => ['Brand', 'swatch'], 'seo' => ['Search & sharing', 'magnifying-glass'], 'advanced' => ['Advanced', 'wrench-screwdriver']];
@endphp

@section('content')
<div x-data="{ d: { values: {}, files: {}, timezones: [], currencies: {} }, loading: true, busy: false, errors: {},
               tab: ['general', 'regional', 'brand', 'seo', 'advanced'].includes(location.hash.slice(1)) ? location.hash.slice(1) : 'general',
               async load() { this.d = await (await fetch('{{ route('admin.system.index') }}', { headers: { Accept: 'application/json' } })).json(); this.loading = false },
               async save(form) { this.busy = true; this.errors = {};
                 const f = new FormData(form); f.set('_method', 'PUT');
                 try { await sharet.send('POST', '{{ route('system.update') }}', f); await this.load() }
                 catch (e) { this.errors = Object.fromEntries(Object.entries(e.errors || {}).map(([k, v]) => [k, v[0]])); sharet.toast('Check the highlighted fields.', 'error') }
                 this.busy = false } }"
     x-init="load()" x-effect="history.replaceState(null, '', '#' + tab)">
  <x-ui.page-header title="System" subtitle="Platform-wide settings for sharet.africa and every business on it.">
    <x-ui.button variant="secondary" icon="arrow-path" @click="sharet.send('POST', '{{ route('system.clear-cache') }}').catch(() => {})">Clear cache</x-ui.button>
  </x-ui.page-header>

  <x-ui.stats :cols="4" class="mb-6">
    <x-ui.stat label="Site name" value="d.values.name" format="raw" icon="globe-alt" tone="lime" />
    <x-ui.stat label="Currency" value="d.values.currency" format="raw" icon="banknotes" tone="emerald" />
    <x-ui.stat label="Time zone" value="d.values.timezone" format="raw" icon="clock" tone="sky" />
    <x-ui.stat label="Maintenance" value="d.maintenance ? 'On' : 'Off'" format="raw" icon="wrench-screwdriver" tone="amber" />
  </x-ui.stats>

  <div class="grid gap-6 lg:grid-cols-[240px_minmax(0,1fr)]">
    <nav class="flex gap-1 overflow-x-auto rounded-2xl border border-gray-200 bg-white p-2 dark:border-gray-800 dark:bg-white/[0.03] lg:h-fit lg:flex-col" aria-label="System settings">
      @foreach ($tabs as $key => [$label, $icon])
        <button type="button" @click="tab = '{{ $key }}'" :class="tab === '{{ $key }}' ? 'bg-primary text-gray-900' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5'"
                class="flex items-center gap-3 whitespace-nowrap rounded-lg px-3 py-2.5 text-left text-sm font-medium">
          <x-dynamic-component :component="'heroicon-o-'.$icon" class="h-5 w-5" /> {{ $label }}
        </button>
      @endforeach
    </nav>

    <form x-show="!loading" x-cloak @submit.prevent="save($el)" class="space-y-6" enctype="multipart/form-data">
      <div x-show="tab === 'general'">@include('admin.system.partials.general')</div>
      <div x-show="tab === 'regional'">@include('admin.system.partials.regional')</div>
      <div x-show="tab === 'brand'">@include('admin.system.partials.brand')</div>
      <div x-show="tab === 'seo'">@include('admin.system.partials.seo')</div>
      <div x-show="tab === 'advanced'">@include('admin.system.partials.advanced')</div>
      <div class="flex justify-end"><x-ui.button type="submit" icon="check" x-bind:disabled="busy">Save settings</x-ui.button></div>
    </form>
  </div>
</div>
@endsection
