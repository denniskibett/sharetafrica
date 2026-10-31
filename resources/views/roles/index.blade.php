{{-- Roles & permissions (Spatie). Data: RoleController@index as JSON. Built-in roles keep their names; their permissions can change.
     Editing opens the slide-over: permissions grouped by area, with switches and "all in this area". --}}
@extends('layouts.app')
@section('title', 'Roles & permissions')

@section('content')
<div x-data="rolesPage('{{ route('admin.roles.index') }}')">
  <x-ui.page-header title="Roles & permissions" subtitle="Choose what each role can open in the dashboard. Super admins can do everything.">
    <x-ui.button icon="plus" @click="edit(null)">New role</x-ui.button>
  </x-ui.page-header>

  <x-ui.stats :cols="4" class="mb-6">
    <x-ui.stat label="Roles" value="stats.roles" hint="stats.roles_hint" icon="shield-check" tone="violet" />
    <x-ui.stat label="Custom roles" value="stats.custom" hint="stats.custom_hint" icon="sparkles" tone="amber" />
    <x-ui.stat label="Permissions" value="stats.permissions" hint="stats.permissions_hint" icon="key" tone="sky" />
    <x-ui.stat label="People with a role" value="stats.people" hint="stats.people_hint" icon="users" tone="emerald" />
  </x-ui.stats>

  <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
      <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Roles</h3>
      <div class="relative">
        <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
        <input type="search" x-model="search" placeholder="Search roles…" aria-label="Search roles" class="h-10 w-full rounded-lg border border-gray-300 bg-transparent pl-11 pr-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 sm:w-[280px]">
      </div>
    </div>
    <div class="custom-scrollbar overflow-x-auto">
      <table class="min-w-full text-left text-sm">
        <thead class="border-y border-gray-100 bg-gray-50 text-xs text-gray-500 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">
          <tr><th class="px-6 py-3 font-medium">Role</th><th class="px-6 py-3 font-medium">Access</th><th class="px-6 py-3 font-medium">People</th><th class="px-6 py-3 font-medium">Type</th><th class="px-6 py-3 text-right font-medium">Action</th></tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
          <tr x-show="loading"><td colspan="5" class="px-6 py-12 text-center text-gray-500">Loading…</td></tr>
          <template x-for="r in filtered" :key="r.id">
            <tr class="text-gray-700 hover:bg-gray-50/60 dark:text-gray-400 dark:hover:bg-white/[0.02]">
              <td class="px-6 py-3">
                <p class="font-medium text-gray-800 dark:text-white/90" x-text="r.label"></p>
                <p class="max-w-md text-xs text-gray-500 dark:text-gray-400" x-text="r.description"></p>
              </td>
              <td class="whitespace-nowrap px-6 py-3">
                <div class="flex items-center gap-3">
                  <div class="h-1.5 w-24 rounded-full bg-gray-100 dark:bg-white/5"><div class="h-1.5 rounded-full bg-gray-900 dark:bg-[#D4FF3D]" :style="`width: ${total ? Math.round(r.permissions.length / total * 100) : 0}%`"></div></div>
                  <span class="text-xs" x-text="r.name === 'super_admin' ? 'Everything' : r.permissions.length + ' of ' + total"></span>
                </div>
              </td>
              <td class="whitespace-nowrap px-6 py-3" x-text="sharet.number(r.users)"></td>
              <td class="whitespace-nowrap px-6 py-3"><span :class="sharet.pill(r.protected ? 'neutral' : 'info')" x-text="r.protected ? 'Built-in' : 'Custom'"></span></td>
              <td class="whitespace-nowrap px-6 py-3 text-right">
                <x-ui.button variant="secondary" class="!h-9" icon="pencil-square" @click="edit(r)">Edit</x-ui.button>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>

  <x-ui.slide-over name="role" width="max-w-2xl" x-text="form.id ? 'Edit ' + form.label : 'New role'">
    <form id="role-form" @submit.prevent="save()" class="space-y-6">
      <x-ui.field label="Role name" name="name" required x-bind:disabled="form.protected" help="Lowercase with underscores, e.g. finance_officer. Built-in roles cannot be renamed." />

      <div class="relative">
        <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
        <input type="search" x-model="permQuery" placeholder="Find a permission…" aria-label="Find a permission" class="h-10 w-full rounded-lg border border-gray-300 bg-transparent pl-11 pr-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
      </div>

      <template x-for="g in visibleGroups" :key="g.key">
        <section class="rounded-xl border border-gray-200 dark:border-gray-800">
          <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-4 py-3 dark:border-gray-800">
            <div>
              <h4 class="font-medium text-gray-900 dark:text-white" x-text="g.label"></h4>
              <p class="text-xs text-gray-500" x-text="g.permissions.filter(p => form.permissions.includes(p.id)).length + ' of ' + g.permissions.length + ' on'"></p>
            </div>
            <button type="button" @click="toggleGroup(g)" class="text-xs font-medium text-gray-700 underline-offset-2 hover:underline dark:text-gray-300" x-text="groupOn(g) ? 'Turn all off' : 'Turn all on'"></button>
          </div>
          <ul class="divide-y divide-gray-100 dark:divide-gray-800">
            <template x-for="p in g.permissions.filter(p => match(p))" :key="p.id">
              <li class="flex items-center justify-between gap-4 px-4 py-3">
                <span class="min-w-0"><span class="block text-sm text-gray-800 dark:text-gray-200" x-text="p.label"></span><code class="text-[11px] text-gray-400" x-text="p.name"></code></span>
                <button type="button" role="switch" :aria-checked="form.permissions.includes(p.id)" @click="toggle(p.id)"
                        class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition"
                        :class="form.permissions.includes(p.id) ? 'bg-gray-900 dark:bg-[#D4FF3D]' : 'bg-gray-200 dark:bg-white/10'">
                  <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition dark:bg-gray-900" :class="form.permissions.includes(p.id) ? 'translate-x-5' : 'translate-x-0.5'"></span>
                </button>
              </li>
            </template>
          </ul>
        </section>
      </template>
    </form>
    <x-slot:footer>
      <x-ui.button variant="danger" x-show="form.id && !form.protected" class="mr-auto" icon="trash" @click="destroy()">Delete role</x-ui.button>
      <x-ui.button variant="secondary" @click="close()">Cancel</x-ui.button>
      <x-ui.button type="submit" form="role-form" x-bind:disabled="busy" x-text="form.id ? 'Save changes' : 'Create role'"></x-ui.button>
    </x-slot:footer>
  </x-ui.slide-over>
</div>
@endsection

@push('scripts')
<script>
  function rolesPage(url) {
    return {
      roles: [], groups: [], stats: {}, loading: true, search: '', permQuery: '', busy: false, errors: {},
      form: { id: null, name: '', label: '', protected: false, permissions: [] },
      async init() { await this.load(); },
      async load() {
        const body = await (await fetch(url, { headers: { Accept: 'application/json' } })).json();
        Object.assign(this, { roles: body.roles, groups: body.groups, stats: body.stats, loading: false });
      },
      get total() { return this.groups.reduce((n, g) => n + g.permissions.length, 0); },
      get filtered() { const q = this.search.toLowerCase(); return this.roles.filter(r => (r.label + r.description).toLowerCase().includes(q)); },
      match(p) { const q = this.permQuery.toLowerCase(); return !q || (p.label + p.name).toLowerCase().includes(q); },
      get visibleGroups() { return this.groups.filter(g => g.permissions.some(p => this.match(p))); },
      edit(r) {
        this.errors = {}; this.permQuery = '';
        this.form = r ? { id: r.id, name: r.name, label: r.label, protected: r.protected, permissions: [...r.permissions] } : { id: null, name: '', label: '', protected: false, permissions: [] };
        sharet.panel('role');
      },
      toggle(id) { const i = this.form.permissions.indexOf(id); i >= 0 ? this.form.permissions.splice(i, 1) : this.form.permissions.push(id); },
      groupOn(g) { return g.permissions.every(p => this.form.permissions.includes(p.id)); },
      toggleGroup(g) {
        const ids = g.permissions.map(p => p.id);
        this.form.permissions = this.groupOn(g) ? this.form.permissions.filter(id => !ids.includes(id)) : [...new Set([...this.form.permissions, ...ids])];
      },
      async save() {
        this.busy = true; this.errors = {};
        try {
          await sharet.send(this.form.id ? 'PUT' : 'POST', this.form.id ? `${url}/${this.form.id}` : url, { name: this.form.name, permissions: this.form.permissions });
          window.dispatchEvent(new CustomEvent('slide-over-close')); await this.load();
        } catch (e) { this.errors = Object.fromEntries(Object.entries(e.errors || {}).map(([k, v]) => [k, v[0]])); }
        this.busy = false;
      },
      async destroy() {
        if (!await sharet.confirm({ title: `Delete ${this.form.label}?`, message: 'People with only this role lose the access it gave them.', tone: 'error', confirmText: 'Delete role' })) return;
        try { await sharet.send('DELETE', `${url}/${this.form.id}`); window.dispatchEvent(new CustomEvent('slide-over-close')); await this.load(); } catch (e) {}
      },
    };
  }
</script>
@endpush
