{{-- API keys (TailAdmin "API Keys" design). Full key only appears once, in the "copy it now" box. --}}
@extends('layouts.app')
@section('title', 'API keys')

@section('content')
@php
    $input = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-violet-400 focus:outline-none focus:ring-4 focus:ring-violet-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
@endphp

<div x-data="apiKeysPage(@js($keys), @js(url('api-keys')))">
  <div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">API keys</h1>
    <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
      Keys let your systems call the Sharet API. Send one as <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs dark:bg-white/10">Authorization: Bearer sk_…</code>.
      We keep only a fingerprint of each key, so copy it when it is shown.
    </p>
  </div>

  {{-- Newly issued key: shown once --}}
  <div x-show="plain" x-cloak x-transition class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-5 dark:border-emerald-500/30 dark:bg-emerald-500/10">
    <p class="mb-3 text-sm font-medium text-emerald-800 dark:text-emerald-200">Copy this key now. It will not be shown again.</p>
    <div class="flex flex-col gap-2 sm:flex-row">
      <input readonly :value="plain" class="{{ $input }} font-mono" @focus="$event.target.select()">
      <button type="button" @click="sharet.copy(plain)" class="inline-flex h-11 shrink-0 items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 text-sm font-medium text-white hover:bg-emerald-700">
        <x-heroicon-o-clipboard-document class="h-5 w-5" /> Copy
      </button>
      <button type="button" @click="plain = null" class="h-11 shrink-0 rounded-lg px-4 text-sm font-medium text-emerald-800 hover:bg-emerald-100 dark:text-emerald-200 dark:hover:bg-emerald-500/10">I've saved it</button>
    </div>
  </div>

  <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="flex flex-col justify-between gap-4 border-b border-gray-100 px-5 py-4 dark:border-gray-800 sm:flex-row sm:items-center">
      <div>
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Your keys</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">Test keys work in the sandbox only. Live keys move real money.</p>
      </div>
      <button type="button" @click="creating = true; form = { name: '', environment: 'test' }; errors = {}"
              class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-violet-600 px-4 text-sm font-medium text-white hover:bg-violet-700">
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
                  <button type="button" @click="rotate(k)" title="Regenerate" class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5">
                    <x-heroicon-o-arrow-path class="h-4 w-4" />
                  </button>
                </div>
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
                <button type="button" role="switch" :aria-checked="k.enabled" @click="toggle(k)" class="relative h-6 w-11 rounded-full transition" :class="k.enabled ? 'bg-violet-500' : 'bg-gray-200 dark:bg-white/10'">
                  <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition" :class="k.enabled && 'translate-x-5'"></span>
                </button>
              </td>
              <td class="whitespace-nowrap px-5 py-3">
                <button type="button" @click="removing = k" title="Delete" class="text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400">
                  <x-heroicon-o-trash class="h-5 w-5" />
                </button>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>

  {{-- Create --}}
  <div x-show="creating" x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center p-4" @keydown.escape.window="creating = false">
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="creating = false"></div>
    <form x-show="creating" x-transition @submit.prevent="create()" class="relative w-full max-w-[560px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-8">
      <h4 class="mb-1 text-xl font-semibold text-gray-800 dark:text-white/90">Generate API key</h4>
      <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">Name it after the system that will use it, so you can tell keys apart later.</p>
      <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300" for="key-name">Application name</label>
      <input id="key-name" x-model="form.name" class="{{ $input }}" placeholder="e.g. Shop checkout" maxlength="80" required>
      <p x-show="errors.name" class="mt-1 text-xs text-red-600" x-text="errors.name"></p>
      <fieldset class="mt-5">
        <legend class="mb-1.5 text-sm font-medium text-gray-700 dark:text-gray-300">Environment</legend>
        <div class="grid grid-cols-2 gap-3">
          <label class="flex cursor-pointer items-center gap-2 rounded-lg border px-4 py-3 text-sm" :class="form.environment === 'test' ? 'border-violet-500 bg-violet-50 dark:bg-violet-500/10' : 'border-gray-300 dark:border-gray-700'">
            <input type="radio" value="test" x-model="form.environment" class="text-violet-600"> Test (sandbox)
          </label>
          <label class="flex cursor-pointer items-center gap-2 rounded-lg border px-4 py-3 text-sm" :class="form.environment === 'live' ? 'border-rose-500 bg-rose-50 dark:bg-rose-500/10' : 'border-gray-300 dark:border-gray-700'">
            <input type="radio" value="live" x-model="form.environment" class="text-rose-600"> Live
          </label>
        </div>
      </fieldset>
      <div class="mt-8 flex gap-3">
        <button type="button" @click="creating = false" class="h-11 flex-1 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">Close</button>
        <button type="submit" :disabled="busy" class="h-11 flex-1 rounded-lg bg-violet-600 text-sm font-medium text-white hover:bg-violet-700 disabled:opacity-50">Generate API key</button>
      </div>
    </form>
  </div>

  {{-- Delete confirm --}}
  <div x-show="removing" x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center p-4" @keydown.escape.window="removing = null">
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="removing = null"></div>
    <div x-show="removing" x-transition class="relative w-full max-w-[460px] rounded-3xl bg-white p-6 text-center dark:bg-gray-900 lg:p-8">
      <h4 class="mb-2 text-xl font-semibold text-gray-800 dark:text-white/90" x-text="'Delete ' + (removing?.name || '') + '?'"></h4>
      <p class="text-sm text-gray-500 dark:text-gray-400">Any system using this key stops working at once. This cannot be undone.</p>
      <div class="mt-7 flex justify-center gap-3">
        <button type="button" @click="removing = null" class="h-11 rounded-lg border border-gray-300 px-5 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">No, keep it</button>
        <button type="button" @click="destroy()" class="h-11 rounded-lg bg-red-600 px-5 text-sm font-medium text-white hover:bg-red-700">Yes, delete</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  function apiKeysPage(keys, base) {
    return {
      keys, plain: null, creating: false, removing: null, form: {}, errors: {}, busy: false,
      put(k) { this.keys = this.keys.some(x => x.id === k.id) ? this.keys.map(x => x.id === k.id ? k : x) : [k, ...this.keys]; },
      async create() {
        this.busy = true; this.errors = {};
        try { const r = await sharet.send('POST', base, this.form); this.put(r.key); this.plain = r.plain; this.creating = false; }
        catch (e) { this.errors = Object.fromEntries(Object.entries(e.errors || {}).map(([k, v]) => [k, v[0]])); }
        this.busy = false;
      },
      async toggle(k) { try { const r = await sharet.send('PATCH', `${base}/${k.id}/toggle`); this.put(r.key); } catch (e) {} },
      async rotate(k) {
        if (!confirm(`Regenerate "${k.name}"? The current key stops working immediately.`)) return;
        try { const r = await sharet.send('POST', `${base}/${k.id}/rotate`); this.put(r.key); this.plain = r.plain; window.scrollTo({ top: 0, behavior: 'smooth' }); } catch (e) {}
      },
      async destroy() {
        const k = this.removing;
        try { await sharet.send('DELETE', `${base}/${k.id}`); this.keys = this.keys.filter(x => x.id !== k.id); this.removing = null; } catch (e) {}
      },
    };
  }
</script>
@endpush
