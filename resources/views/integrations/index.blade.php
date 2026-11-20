{{-- Integrations: provider cards (TailAdmin "Integrations" design). Saves in the background via sharet.send(). --}}
@extends('layouts.app')
@section('title', 'Integrations')

@section('content')
@php
    $scopeParam = $scope?->id ?? 'platform';
    $base = url('integrations');
    $input = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-violet-400 focus:outline-none focus:ring-4 focus:ring-violet-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30';
@endphp

<div x-data="integrationsPage('{{ $base }}', '{{ $scopeParam }}')">
  <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
      <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Integrations</h1>
      <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
        Connect payment rails, SMS and email from here instead of the server's .env file.
        Keys are encrypted when saved and never shown again.
        @if ($isAdmin) Platform settings apply to every business that has not connected its own. @endif
      </p>
    </div>

    @if ($isAdmin)
      <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
        <span class="shrink-0">Settings for</span>
        <select class="{{ $input }} sm:w-64" onchange="location.search = '?company=' + this.value">
          <option value="platform" @selected(! $scope)>Sharet platform (default)</option>
          @foreach ($companies as $company)
            <option value="{{ $company->id }}" @selected($scope?->id === $company->id)>{{ $company->name }}</option>
          @endforeach
        </select>
      </label>
    @else
      <span class="rounded-full bg-violet-100 px-3 py-1 text-sm font-medium text-violet-700 dark:bg-violet-500/15 dark:text-violet-300">{{ $scope?->name }}</span>
    @endif
  </div>

  {{-- Category filter --}}
  <div class="mb-5 flex flex-wrap gap-2">
    <template x-for="c in ['All', ...categories]" :key="c">
      <button type="button" @click="filter = c" x-text="c"
              class="rounded-full border px-3 py-1.5 text-sm font-medium transition"
              :class="filter === c ? 'border-gray-900 bg-gray-900 text-white dark:border-white dark:bg-white dark:text-gray-900' : 'border-gray-200 text-gray-600 hover:bg-gray-100 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-white/5'"></button>
    </template>
  </div>

  <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
    <template x-for="p in shown" :key="p.key">
      <article class="flex flex-col rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="relative flex-1 p-5 pb-7">
          <div class="mb-5 flex items-center gap-3">
            <span x-show="p.logo" class="flex h-11 w-11 items-center justify-center overflow-hidden rounded-xl bg-white p-1.5 shadow-sm ring-1 ring-gray-200 dark:ring-white/10">
              <img :src="p.logo" :alt="p.name + ' logo'" class="max-h-full max-w-full object-contain" loading="lazy">
            </span>
            <span x-show="!p.logo" class="flex h-11 w-11 items-center justify-center rounded-xl text-sm font-bold text-white shadow-sm"
                  :style="`background:${p.color}`" x-text="initials(p.name)"></span>
            <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-white/5 dark:text-gray-300" x-text="p.category"></span>
          </div>
          <h3 class="mb-2 flex items-center gap-2 text-lg font-semibold text-gray-800 dark:text-white/90"><span x-text="p.name"></span>
            <span x-show="p.environment" :class="sharet.pill(p.environment === 'live' ? 'success' : 'info')" x-text="p.environment === 'live' ? 'Live' : 'Sandbox'"></span></h3>
          <p class="text-sm text-gray-500 dark:text-gray-400" x-text="p.description"></p>

          <p class="mt-4 flex items-center gap-2 text-xs font-medium">
            <span class="h-2 w-2 rounded-full" :class="!p.connected ? 'bg-gray-300 dark:bg-gray-600' : (p.enabled ? (p.last_test_ok === false ? 'bg-amber-500' : 'bg-emerald-500') : 'bg-gray-400')"></span>
            <span class="text-gray-500 dark:text-gray-400" x-text="status(p)"></span>
          </p>

          <div x-show="p.connected" x-data="{ menu: false }" class="absolute right-4 top-4" @click.outside="menu = false">
            <button type="button" @click="menu = !menu" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/5 dark:hover:text-white" aria-label="More">
              <x-heroicon-o-ellipsis-horizontal class="h-5 w-5" />
            </button>
            <div x-show="menu" x-cloak x-transition class="absolute right-0 z-10 mt-1 w-40 rounded-xl border border-gray-200 bg-white p-1.5 shadow-lg dark:border-gray-800 dark:bg-gray-900">
              <button type="button" @click="menu = false; confirmRemove(p)" class="w-full rounded-lg px-3 py-2 text-left text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10">Remove</button>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-between gap-3 border-t border-gray-200 p-5 dark:border-gray-800">
          <div class="flex gap-2">
            <button type="button" @click="openSettings(p)" class="inline-flex h-10 items-center gap-2 rounded-lg border border-gray-300 px-3 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
              <x-heroicon-o-cog-6-tooth class="h-5 w-5" /> <span x-text="p.connected ? 'Settings' : 'Connect'"></span>
            </button>
            <button type="button" x-show="p.connected" @click="details = p" class="inline-flex h-10 items-center rounded-lg border border-gray-300 px-3 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">Details</button>
          </div>
          {{-- Grouped providers (M-Pesa live / sandbox): a radio, only one can be in use --}}
          <label x-show="p.group" class="flex cursor-pointer items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300" :class="!p.connected && 'cursor-not-allowed opacity-40'">
            <input type="radio" :name="'group-' + p.group" :checked="p.enabled" :disabled="!p.connected" @click.prevent="!p.enabled && toggle(p)"
                   class="h-5 w-5 border-gray-300 text-gray-900 focus:ring-gray-500 dark:border-gray-600 dark:bg-gray-900 dark:checked:bg-[#D4FF3D]">
            <span x-text="p.enabled ? 'In use' : 'Use this one'"></span>
          </label>
          <button type="button" x-show="!p.group" role="switch" :aria-checked="p.enabled" :disabled="!p.connected" @click="toggle(p)"
                  :title="p.connected ? (p.enabled ? 'Turn off' : 'Turn on') : 'Connect first'"
                  class="relative h-6 w-11 shrink-0 rounded-full transition disabled:opacity-40"
                  :class="p.enabled ? 'bg-gray-900 dark:bg-[#D4FF3D]' : 'bg-gray-200 dark:bg-white/10'">
            <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition" :class="p.enabled && 'translate-x-5'"></span>
          </button>
        </div>
      </article>
    </template>
  </div>

  {{-- Settings modal --}}
  <div x-show="editing" x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center overflow-y-auto p-4" @keydown.escape.window="editing = null">
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="editing = null"></div>
    <form x-show="editing" x-transition @submit.prevent="save()" class="relative w-full max-w-[560px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-8">
      <button type="button" @click="editing = null" class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400" aria-label="Close">
        <x-heroicon-o-x-mark class="h-5 w-5" />
      </button>
      <template x-if="editing">
        <div>
          <h4 class="mb-1 text-xl font-semibold text-gray-800 dark:text-white/90" x-text="editing.name"></h4>
          <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">Secret fields are write-only. Leave them blank to keep what is saved.</p>
          <div x-show="editing.fields.some(f => f.sandbox !== undefined || f.key === 'environment')" class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-xl bg-sky-50 px-4 py-3 text-sm text-sky-800 dark:bg-sky-500/10 dark:text-sky-200">
            <span>Test first: the sandbox moves no real money.</span>
            <button type="button" @click="useSandbox()" class="font-medium underline">Use sandbox test values</button>
          </div>

          <div class="space-y-4">
            <template x-for="f in editing.fields" :key="f.key">
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300" :for="'f-' + f.key">
                  <span x-text="f.label"></span><span x-show="f.required" class="text-red-500"> *</span>
                </label>
                <template x-if="f.type === 'select'">
                  <select :id="'f-' + f.key" x-model="form[f.key]" class="{{ $input }}">
                    <option value="">Choose…</option>
                    <template x-for="(label, value) in f.options" :key="value"><option :value="value" x-text="label"></option></template>
                  </select>
                </template>
                <template x-if="f.type !== 'select'">
                  <input :id="'f-' + f.key" x-model="form[f.key]" class="{{ $input }}" autocomplete="off"
                         :type="f.secret ? 'password' : f.type"
                         :placeholder="f.secret && editing.saved_secrets.includes(f.key) ? 'Saved • leave blank to keep' : (f.placeholder || '')">
                </template>
                <p x-show="f.help" class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-text="f.help"></p>
                <p x-show="errors['values.' + f.key]" class="mt-1 text-xs text-red-600 dark:text-red-400" x-text="errors['values.' + f.key]"></p>
              </div>
            </template>
          </div>

          <div x-show="editing.last_test_message" class="mt-5 rounded-xl px-4 py-3 text-sm"
               :class="editing.last_test_ok ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-200' : 'bg-amber-50 text-amber-800 dark:bg-amber-500/10 dark:text-amber-200'">
            <span x-text="editing.last_test_message"></span> <span class="opacity-70" x-text="editing.last_tested_at ? '(' + editing.last_tested_at + ')' : ''"></span>
          </div>

          <div class="mt-7 flex flex-col-reverse gap-3 sm:flex-row">
            <button type="button" x-show="editing.connected && editing.key === 'smtp'" @click="sendTestEmail()" :disabled="busy"
                    class="flex h-11 flex-1 items-center justify-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">Send test email</button>
            <button type="button" x-show="editing.connected" @click="test(editing)" :disabled="busy"
                    class="flex h-11 flex-1 items-center justify-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">Test connection</button>
            <button type="submit" :disabled="busy"
                    class="flex h-11 flex-1 items-center justify-center rounded-lg bg-violet-600 px-4 text-sm font-medium text-white hover:bg-violet-700 disabled:opacity-50">
              <span x-text="busy ? 'Saving…' : 'Save'"></span>
            </button>
          </div>
        </div>
      </template>
    </form>
  </div>

  {{-- Details modal --}}
  <div x-show="details" x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center p-4" @keydown.escape.window="details = null">
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="details = null"></div>
    <div x-show="details" x-transition class="relative w-full max-w-[560px] rounded-3xl bg-white p-6 dark:bg-gray-900 lg:p-8">
      <button type="button" @click="details = null" class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-500 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400" aria-label="Close">
        <x-heroicon-o-x-mark class="h-5 w-5" />
      </button>
      <template x-if="details">
        <div>
          <h4 class="mb-1 text-xl font-semibold text-gray-800 dark:text-white/90" x-text="details.name + ' details'"></h4>
          <p class="mb-6 text-sm text-gray-500 dark:text-gray-400" x-text="'Last saved ' + (details.updated_at || '—')"></p>
          <ul class="divide-y divide-gray-100 dark:divide-gray-800">
            <template x-for="f in details.fields" :key="f.key">
              <li class="flex justify-between gap-4 py-2.5 text-sm">
                <span class="text-gray-500 dark:text-gray-400" x-text="f.label"></span>
                <span class="break-all text-right font-medium text-gray-800 dark:text-gray-200"
                      x-text="f.secret ? (details.saved_secrets.includes(f.key) ? '•••••••• saved' : 'Not set') : (display(f, details.values[f.key]) || 'Not set')"></span>
              </li>
            </template>
            <li class="flex justify-between gap-4 py-2.5 text-sm">
              <span class="text-gray-500 dark:text-gray-400">Last test</span>
              <span class="text-right font-medium text-gray-800 dark:text-gray-200" x-text="details.last_tested_at ? (details.last_test_ok ? 'Passed ' : 'Failed ') + details.last_tested_at : 'Not tested'"></span>
            </li>
          </ul>
        </div>
      </template>
    </div>
  </div>

  {{-- Remove confirm --}}
  <div x-show="removing" x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center p-4" @keydown.escape.window="removing = null">
    <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" @click="removing = null"></div>
    <div x-show="removing" x-transition class="relative w-full max-w-[460px] rounded-3xl bg-white p-6 text-center dark:bg-gray-900 lg:p-8">
      <span class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-500/15 dark:text-red-400">
        <x-heroicon-o-trash class="h-7 w-7" />
      </span>
      <h4 class="mb-2 text-xl font-semibold text-gray-800 dark:text-white/90" x-text="'Remove ' + (removing?.name || '') + '?'"></h4>
      <p class="text-sm text-gray-500 dark:text-gray-400">The saved keys are deleted. Anything using this connection falls back to the platform settings, or stops.</p>
      <div class="mt-7 flex justify-center gap-3">
        <button type="button" @click="removing = null" class="h-11 rounded-lg border border-gray-300 px-5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">No, keep it</button>
        <button type="button" @click="remove()" :disabled="busy" class="h-11 rounded-lg bg-red-600 px-5 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50">Yes, remove</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  function integrationsPage(base, scope) {
    return {
      providers: [],
      async init() { this.providers = (await (await fetch(`${base}?company=${scope}`, { headers: { Accept: 'application/json' } })).json()).providers; },
      filter: 'All', editing: null, details: null, removing: null, form: {}, errors: {}, busy: false,
      get categories() { return [...new Set(this.providers.map(p => p.category))]; },
      get shown() { return this.filter === 'All' ? this.providers : this.providers.filter(p => p.category === this.filter); },
      url(p, tail = '') { return `${base}/${p.key}${tail}?company=${scope}`; },
      initials(name) { return name.replace(/[^A-Za-z ]/g, '').split(' ').filter(Boolean).slice(0, 2).map(w => w[0]).join('').toUpperCase(); },
      display(f, v) { return f.type === 'select' && f.options ? (f.options[v] ?? v) : v; },
      status(p) {
        if (!p.connected) return 'Not connected';
        if (!p.enabled) return 'Connected · turned off';
        if (p.last_test_ok === false) return 'On · last test failed';
        return p.last_test_ok ? 'On · tested ' + p.last_tested_at : 'On · not tested yet';
      },
      replace(updated, siblings = []) {
        [updated, ...siblings].forEach(u => { this.providers = this.providers.map(p => p.key === u.key ? u : p); });
        if (this.editing?.key === updated.key) this.editing = updated;
      },
      openSettings(p) {
        this.errors = {};
        // A new connection starts in the provider's sandbox, with its published test values filled in
        this.form = Object.fromEntries(p.fields.map(f => [f.key, f.secret && p.connected ? '' : (p.values[f.key] ?? (p.connected ? '' : f.sandbox) ?? (f.type === 'select' ? Object.keys(f.options)[0] : ''))]));
        this.editing = p;
      },
      useSandbox() {
        this.editing.fields.forEach(f => { if (f.sandbox !== undefined) this.form[f.key] = f.sandbox; });
        if ('environment' in this.form) this.form.environment = 'sandbox';
      },
      async save() {
        this.busy = true; this.errors = {};
        try {
          const r = await sharet.send('PUT', this.url(this.editing), { values: this.form });
          this.replace(r.provider, r.siblings);
          Object.keys(this.form).forEach(k => { if (r.provider.saved_secrets.includes(k)) this.form[k] = ''; });
        } catch (e) { this.errors = e.errors || {}; }
        this.busy = false;
      },
      async test(p) {
        this.busy = true;
        try { const r = await sharet.send('POST', this.url(p, '/test')); this.replace(r.provider); }
        catch (e) { if (e.body?.provider) this.replace(e.body.provider); }
        this.busy = false;
      },
      async sendTestEmail() {
        this.busy = true;
        try { const r = await sharet.send('POST', `${base}/smtp/send-test?company=${scope}`); this.replace(r.provider); }
        catch (e) { if (e.body?.provider) this.replace(e.body.provider); }
        this.busy = false;
      },
      async toggle(p) {
        if (!p.connected) return;
        try { const r = await sharet.send('PATCH', this.url(p, '/toggle')); this.replace(r.provider, r.siblings); } catch (e) {}
      },
      confirmRemove(p) { this.removing = p; },
      async remove() {
        this.busy = true;
        try { const r = await sharet.send('DELETE', this.url(this.removing)); this.replace(r.provider); this.removing = null; } catch (e) {}
        this.busy = false;
      },
    };
  }
</script>
@endpush
