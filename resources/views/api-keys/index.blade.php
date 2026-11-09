{{-- API keys (TailAdmin "API Keys" design). Full key only appears once, in the "copy it now" box. --}}
@extends('layouts.app')
@section('title', 'API keys')

@section('content')
@php
    $input = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-violet-400 focus:outline-none focus:ring-4 focus:ring-violet-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
@endphp

<div x-data="apiKeysPage('{{ url('api-keys') }}')">
  <div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">API keys</h1>
    <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
      Keys let your systems call the Sharet API. Send one as <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs dark:bg-white/10">Authorization: Bearer sk_…</code>.
      We keep only a fingerprint of each key, so copy it when it is shown.
    </p>
  </div>

  @if ($wallet)
    <div class="mb-6 flex flex-col gap-1 rounded-2xl border border-gray-200 bg-white px-5 py-4 dark:border-gray-800 dark:bg-white/[0.03] sm:flex-row sm:items-center sm:gap-4">
      <p class="text-sm text-gray-500 dark:text-gray-400">Your keys act for <b class="text-gray-900 dark:text-white">{{ $company->name }}</b></p>
      <code class="rounded-lg bg-gray-900 px-2.5 py-1 font-mono text-sm text-[#D4FF3D]">{{ $wallet->sharet_id }}</code>
      <span class="text-sm text-gray-500">{{ '@'.$wallet->handle }} · settlements are tied to this Sharet ID</span>
    </div>
  @endif

  {{-- Newly issued key: shown once --}}
  <div x-show="plain" x-cloak x-transition class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-5 dark:border-emerald-500/30 dark:bg-emerald-500/10">
    <p class="mb-3 text-sm font-medium text-emerald-800 dark:text-emerald-200">Copy this key now. It will not be shown again. The examples below now use it, so you can test it straight away.</p>
    <div class="flex flex-col gap-2 sm:flex-row">
      <input readonly :value="plain" class="{{ $input }} font-mono" @focus="$event.target.select()">
      <button type="button" @click="sharet.copy(plain)" class="inline-flex h-11 shrink-0 items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 text-sm font-medium text-white hover:bg-emerald-700">
        <x-heroicon-o-clipboard-document class="h-5 w-5" /> Copy
      </button>
      <button type="button" @click="plain = null" class="h-11 shrink-0 rounded-lg px-4 text-sm font-medium text-emerald-800 hover:bg-emerald-100 dark:text-emerald-200 dark:hover:bg-emerald-500/10">I've saved it</button>
    </div>
  </div>

  @include('api-keys.partials.guide')

  <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="flex flex-col justify-between gap-4 border-b border-gray-100 px-5 py-4 dark:border-gray-800 sm:flex-row sm:items-center">
      <div>
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Your keys</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">Test keys work in the sandbox only. Live keys move real money.</p>
      </div>
      <button type="button" @click="creating = true; form = { name: '', environment: 'test', scopes: [], allowed_ips: '' }; errors = {}"
              class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-gray-900 px-4 text-sm font-medium text-white hover:bg-gray-800 dark:bg-[#D4FF3D] dark:text-gray-900">
        <x-heroicon-o-plus class="h-5 w-5" /> Add API key
      </button>
    </div>

    <p x-show="!keys.length" class="px-5 py-12 text-center text-sm text-gray-500 dark:text-gray-400">No keys yet. Add one to start calling the API.</p>

    <div x-show="keys.length" class="overflow-x-auto">
      <table class="min-w-full text-left">
        <thead>
          <tr class="border-b border-gray-100 text-xs font-medium text-gray-500 dark:border-gray-800 dark:text-gray-400">
            <th class="px-5 py-3">Name</th>
            <th class="px-5 py-3">Status</th>
            @if ($isAdmin)<th class="px-5 py-3">Owner</th>@endif
            <th class="px-5 py-3">Created</th>
            <th class="px-5 py-3">Last used</th>
            <th class="px-5 py-3">On / off</th>
            <th class="px-5 py-3">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
          <template x-for="k in keys" :key="k.id">
            <tr class="text-sm">
              <td class="whitespace-nowrap px-5 py-3">
                <p class="mb-1 flex items-center gap-2 text-gray-800 dark:text-gray-200">
                  <span x-text="k.name"></span>
                  <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase"
                        :class="k.environment === 'live' ? 'bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300' : 'bg-sky-100 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300'" x-text="k.environment"></span>
                </p>
                <div class="flex items-center gap-2">
                  <code class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 font-mono text-xs text-gray-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400" x-text="k.masked"></code>
                </div>
                <div class="mt-1.5 flex flex-wrap gap-1">
                  <template x-for="sc in k.scopes"><span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-700 dark:bg-white/10 dark:text-gray-300" x-text="sc"></span></template>
                  <span x-show="k.allowed_ips.length" class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] text-gray-600 dark:bg-white/10 dark:text-gray-300" x-text="k.allowed_ips.length + ' IP' + (k.allowed_ips.length > 1 ? 's' : '')"></span>
                </div>
                <p x-show="k.payout_blocked" class="mt-1.5 flex items-center gap-1 text-xs text-amber-700 dark:text-amber-300"><x-heroicon-o-exclamation-triangle class="h-4 w-4" /> Add your server IPs to pay out</p>
              </td>
              <td class="whitespace-nowrap px-5 py-3">
                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium"
                      :class="k.enabled ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-300'"
                      x-text="k.enabled ? 'Active' : 'Disabled'"></span>
              </td>
              @if ($isAdmin)<td class="whitespace-nowrap px-5 py-3 text-gray-500 dark:text-gray-400" x-text="k.owner"></td>@endif
              <td class="whitespace-nowrap px-5 py-3 text-gray-500 dark:text-gray-400" x-text="k.created_at"></td>
              <td class="whitespace-nowrap px-5 py-3 text-gray-500 dark:text-gray-400" x-text="k.last_used_at"></td>
              <td class="whitespace-nowrap px-5 py-3">
                <button type="button" role="switch" :aria-checked="k.enabled" @click="toggle(k)" class="relative h-6 w-11 rounded-full transition" :class="k.enabled ? 'bg-gray-900 dark:bg-[#D4FF3D]' : 'bg-gray-200 dark:bg-white/10'">
                  <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition" :class="k.enabled && 'translate-x-5'"></span>
                </button>
              </td>
              <td class="whitespace-nowrap px-5 py-3">
                <x-ui.row-menu>
                  <x-ui.row-menu-item icon="clipboard-document" @click="copySecret(k)">Copy full key</x-ui.row-menu-item>
                  <x-ui.row-menu-item icon="finger-print" @click="sharet.copy(k.hint)">Copy key ID</x-ui.row-menu-item>
                  <x-ui.row-menu-item icon="identification" x-show="k.account" @click="sharet.copy(k.account)">Copy Sharet ID</x-ui.row-menu-item>
                  <x-ui.row-menu-item icon="globe-alt" x-show="k.allowed_ips.length" @click="sharet.copy(k.allowed_ips.join(', '))">Copy allowed IPs</x-ui.row-menu-item>
                  <x-ui.row-menu-item icon="adjustments-horizontal" @click="editing = k; access = { scopes: k.scopes.filter(x => x !== 'read'), allowed_ips: k.allowed_ips.join(', ') }; errors = {}">Access</x-ui.row-menu-item>
                  <x-ui.row-menu-item icon="arrow-path" @click="rotate(k)">Regenerate</x-ui.row-menu-item>
                  <x-ui.row-menu-item icon="power" @click="toggle(k)" x-text="k.enabled ? 'Disable' : 'Enable'"></x-ui.row-menu-item>
                  <x-ui.row-menu-item icon="trash" danger @click="destroy(k)">Delete</x-ui.row-menu-item>
                </x-ui.row-menu>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>

  {{-- Create --}}
  <div x-show="creating" x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center p-4" @keydown.escape.window="creating = false">
    <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-[32px]" @click="creating = false"></div>
    <form x-show="creating" x-transition @submit.prevent="create()" class="relative w-full max-w-[560px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-8">
      <h4 class="mb-1 text-xl font-semibold text-gray-800 dark:text-white/90">Generate API key</h4>
      <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">Name it after the system that will use it, so you can tell keys apart later.</p>
      <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300" for="key-name">Application name</label>
      <input id="key-name" x-model="form.name" class="{{ $input }}" placeholder="e.g. Shop checkout" maxlength="80" required>
      <p x-show="errors.name" class="mt-1 text-xs text-red-600" x-text="errors.name"></p>
      <fieldset class="mt-5">
        <legend class="mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">Environment</legend>
        <div class="grid gap-3 sm:grid-cols-2">
          <label class="flex cursor-pointer gap-3 rounded-xl border p-4 text-sm" :class="form.environment === 'test' ? 'border-gray-900 bg-gray-50 dark:border-[#D4FF3D] dark:bg-white/5' : 'border-gray-300 dark:border-gray-700'">
            <input type="radio" value="test" x-model="form.environment" class="mt-0.5 text-gray-900">
            <span><span class="block font-medium text-gray-900 dark:text-white">Test · sandbox</span><span class="mt-1 block text-xs text-gray-500 dark:text-gray-400">Starts with sk_test_. Uses your M-Pesa Sandbox. No real money. Use it while you build.</span></span>
          </label>
          <label class="flex cursor-pointer gap-3 rounded-xl border p-4 text-sm" :class="form.environment === 'live' ? 'border-red-500 bg-red-50 dark:bg-red-500/10' : 'border-gray-300 dark:border-gray-700'">
            <input type="radio" value="live" x-model="form.environment" class="mt-0.5 text-red-600">
            <span><span class="block font-medium text-gray-900 dark:text-white">Live</span><span class="mt-1 block text-xs text-gray-500 dark:text-gray-400">Starts with sk_live_. Uses your M-Pesa Live. Moves real money: only on your server.</span></span>
          </label>
        </div>
      </fieldset>
      <fieldset class="mt-5">
        <legend class="mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">What it can do <span class="font-normal text-gray-500">(every key can read)</span></legend>
        <div class="grid gap-2 sm:grid-cols-3">
          @foreach (['collect' => 'Collect money', 'payout' => 'Pay out', 'connect' => 'Connect accounts'] as $sc => $label)
            <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-gray-300 p-3 text-sm dark:border-gray-700"><input type="checkbox" value="{{ $sc }}" x-model="form.scopes" class="rounded text-gray-900"> {{ $label }}</label>
          @endforeach
        </div>
      </fieldset>
      <div class="mt-5" x-show="form.environment === 'live' && form.scopes.includes('payout')">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Your servers' IP addresses</label>
        <input x-model="form.allowed_ips" class="{{ $input }}" placeholder="41.90.12.7, 196.201.0.0/16">
        <p class="mt-1 text-xs text-gray-500">Live payouts only work from these addresses. Ranges are fine.</p>
        <p x-show="errors.allowed_ips" class="mt-1 text-xs text-red-600" x-text="errors.allowed_ips"></p>
      </div>
      <div class="mt-5 rounded-xl bg-gray-50 p-4 text-xs text-gray-600 dark:bg-white/5 dark:text-gray-300">
        <p class="font-medium text-gray-800 dark:text-white/90">What you are generating</p>
        <p class="mt-1">A secret that lets <span class="font-medium" x-text="form.name || 'your system'"></span> call the Sharet API as your business, in <span x-text="form.environment === 'live' ? 'live mode (real money)' : 'the sandbox (test money)'"></span>. You will see it once; we only keep a fingerprint.</p>
      </div>
      <div class="mt-8 flex gap-3">
        <button type="button" @click="creating = false" class="h-11 flex-1 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">Close</button>
        <button type="submit" :disabled="busy" class="h-11 flex-1 rounded-lg bg-gray-900 text-sm font-medium text-white hover:bg-gray-800 disabled:opacity-50 dark:bg-[#D4FF3D] dark:text-gray-900">Generate API key</button>
      </div>
    </form>
  </div>

  {{-- Access: scopes and allowed IPs --}}
  <div x-show="editing" x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center p-4" @keydown.escape.window="editing = null">
    <div class="fixed inset-0 bg-gray-400/50 backdrop-blur-[32px]" @click="editing = null"></div>
    <form x-show="editing" x-transition @submit.prevent="saveAccess()" class="relative w-full max-w-[560px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-8">
      <h4 class="mb-1 text-xl font-semibold text-gray-800 dark:text-white/90">Access for <span x-text="editing?.name"></span></h4>
      <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">Changes apply to the next request.</p>
      <fieldset class="mt-5">
        <legend class="mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">What it can do <span class="font-normal text-gray-500">(every key can read)</span></legend>
        <div class="grid gap-2 sm:grid-cols-3">
          @foreach (['collect' => 'Collect money', 'payout' => 'Pay out', 'connect' => 'Connect accounts'] as $sc => $label)
            <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-gray-300 p-3 text-sm dark:border-gray-700"><input type="checkbox" value="{{ $sc }}" x-model="access.scopes" class="rounded text-gray-900"> {{ $label }}</label>
          @endforeach
        </div>
      </fieldset>
      <div class="mt-5" x-show="editing?.environment === 'live' && access.scopes.includes('payout')">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Your servers' IP addresses</label>
        <input x-model="access.allowed_ips" class="{{ $input }}" placeholder="41.90.12.7, 196.201.0.0/16">
        <p class="mt-1 text-xs text-gray-500">Live payouts only work from these addresses. Ranges are fine.</p>
        <p x-show="errors.allowed_ips" class="mt-1 text-xs text-red-600" x-text="errors.allowed_ips"></p>
      </div>
      <div class="mt-8 flex gap-3">
        <button type="button" @click="editing = null" class="h-11 flex-1 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">Close</button>
        <button type="submit" :disabled="busy" class="h-11 flex-1 rounded-lg bg-gray-900 text-sm font-medium text-white hover:bg-gray-800 disabled:opacity-50 dark:bg-[#D4FF3D] dark:text-gray-900">Save</button>
      </div>
    </form>
  </div>

  {{-- Delete confirm --}}
</div>
@endsection

@push('scripts')
<script>
  function apiKeysPage(base) {
    return {
      keys: [], plain: null,
      async init() { this.keys = (await (await fetch(base, { headers: { Accept: 'application/json' } })).json()).keys; }, creating: false, removing: null, form: { scopes: [] }, errors: {}, busy: false, editing: null, access: { scopes: [], allowed_ips: '' },
      put(k) { this.keys = this.keys.some(x => x.id === k.id) ? this.keys.map(x => x.id === k.id ? k : x) : [k, ...this.keys]; },
      async create() {
        this.busy = true; this.errors = {};
        try { const r = await sharet.send('POST', base, this.form); this.put(r.key); this.plain = r.plain; this.creating = false; }
        catch (e) { this.errors = Object.fromEntries(Object.entries(e.errors || {}).map(([k, v]) => [k, v[0]])); }
        this.busy = false;
      },
      async saveAccess() {
        this.busy = true; this.errors = {};
        try { const r = await sharet.send('PATCH', `${base}/${this.editing.id}/settings`, this.access); this.put(r.key); this.editing = null; }
        catch (e) { this.errors = Object.fromEntries(Object.entries(e.errors || {}).map(([k, v]) => [k, v[0]])); }
        this.busy = false;
      },
      async toggle(k) { try { const r = await sharet.send('PATCH', `${base}/${k.id}/toggle`); this.put(r.key); } catch (e) {} },
      async copySecret(k) {
        if (!await sharet.confirm({ title: 'The full key was shown only once', message: 'We keep only a fingerprint, so nobody (not even Sharet) can copy it again. If you have lost it, regenerate: you get a new key to copy, and the current one stops working.',
          confirmText: 'Regenerate…', details: [['Name', k.name], ['Key ID', k.hint]] })) return;
        this.rotate(k);
      },
      async rotate(k) {
        if (!await sharet.confirm({ title: 'Regenerate this key?', message: 'The current key stops working immediately. Update your systems with the new one.', confirmText: 'Regenerate',
          details: [['Name', k.name], ['Environment', k.environment], ['Key', k.masked], ['Last used', k.last_used_at]] })) return;
        try { const r = await sharet.send('POST', `${base}/${k.id}/rotate`); this.put(r.key); this.plain = r.plain; window.scrollTo({ top: 0, behavior: 'smooth' }); } catch (e) {}
      },
      async destroy(k) {
        if (!await sharet.confirm({ title: 'Delete this key?', message: 'Requests using it get 401 from now on. This cannot be undone.', tone: 'error', confirmText: 'Delete',
          details: [['Name', k.name], ['Environment', k.environment], ['Key', k.masked]] })) return;
        try { await sharet.send('DELETE', `${base}/${k.id}`); this.keys = this.keys.filter(x => x.id !== k.id); } catch (e) {}
      },
    };
  }
</script>
@endpush
