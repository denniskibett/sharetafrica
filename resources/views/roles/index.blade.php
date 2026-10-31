{{-- Roles & permissions (Spatie). Built-in roles keep their names; their permissions can change. --}}
@extends('layouts.app')
@section('title', 'Roles & permissions')

@section('content')
@php
    $label = fn ($name) => ucfirst(str_replace(['_', '.'], [' ', ' · '], $name));
    $permissionList = $permissions->flatten()->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'label' => $label($p->name), 'group' => explode('.', $p->name)[0]])->values();
    $roleList = $roles->map(fn ($r) => [
        'id' => $r->id, 'name' => $r->name, 'label' => $label($r->name), 'description' => $r->description,
        'users' => $r->users_count, 'protected' => $r->is_protected, 'permissions' => $r->permission_ids,
    ])->values();
@endphp

<div x-data="rolesPage(@js($roleList), @js($permissionList), @js(url('admin/roles')))">
  <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
      <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Roles & permissions</h1>
      <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">Choose what each role can open in the dashboard. Super admins can do everything.</p>
    </div>
    <button type="button" @click="startNew()" class="inline-flex h-11 items-center gap-2 rounded-lg bg-violet-600 px-4 text-sm font-medium text-white hover:bg-violet-700">
      <x-heroicon-o-plus class="h-5 w-5" /> New role
    </button>
  </div>

  <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.2fr)]">
    <ul class="flex flex-col gap-2">
      <template x-for="r in roles" :key="r.id">
        <li>
          <button type="button" @click="select(r)"
                  class="flex w-full items-center gap-4 rounded-2xl border bg-white p-4 text-left transition dark:bg-white/[0.03]"
                  :class="current?.id === r.id ? 'border-violet-400 ring-4 ring-violet-500/10' : 'border-gray-200 hover:border-gray-300 dark:border-gray-800 dark:hover:border-gray-700'">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
              <x-heroicon-o-shield-check class="h-5 w-5" />
            </span>
            <span class="min-w-0 flex-1">
              <span class="flex items-center gap-2 font-medium text-gray-900 dark:text-white">
                <span x-text="r.label"></span>
                <span x-show="r.protected" class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-gray-500 dark:bg-white/5 dark:text-gray-400">Built-in</span>
              </span>
              <span class="block truncate text-xs text-gray-500 dark:text-gray-400" x-text="r.description"></span>
            </span>
            <span class="shrink-0 text-xs text-gray-500 dark:text-gray-400" x-text="r.users + (r.users === 1 ? ' person' : ' people')"></span>
          </button>
        </li>
      </template>
    </ul>

    <form x-show="current" x-cloak @submit.prevent="save()" class="h-fit rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] lg:sticky lg:top-24">
      <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300" for="role-name">Role name</label>
      <input id="role-name" x-model="form.name" :readonly="current?.protected" required
             class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 read-only:bg-gray-50 read-only:text-gray-500 focus:border-violet-400 focus:outline-none dark:border-gray-700 dark:text-white/90 dark:read-only:bg-white/5">
      <p x-show="errors.name" class="mt-1 text-xs text-red-600" x-text="errors.name"></p>

      <template x-for="(items, group) in grouped" :key="group">
        <fieldset class="mt-5">
          <legend class="mb-2 text-xs font-semibold uppercase tracking-wider text-gray-400" x-text="group.replace('_', ' ')"></legend>
          <div class="grid gap-2 sm:grid-cols-2">
            <template x-for="p in items" :key="p.id">
              <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-white/5">
                <input type="checkbox" :value="p.id" x-model.number="form.permissions" class="rounded border-gray-300 text-violet-600 focus:ring-violet-500">
                <span x-text="p.label"></span>
              </label>
            </template>
          </div>
        </fieldset>
      </template>

      <div class="mt-6 flex flex-wrap gap-3">
        <button type="submit" :disabled="busy" class="h-11 rounded-lg bg-violet-600 px-5 text-sm font-medium text-white hover:bg-violet-700 disabled:opacity-50" x-text="current?.id ? 'Save changes' : 'Create role'"></button>
        <button type="button" x-show="current?.id && !current?.protected" @click="destroy()" class="h-11 rounded-lg border border-red-200 px-5 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-500/30 dark:text-red-400 dark:hover:bg-red-500/10">Delete role</button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
  function rolesPage(roles, permissions, base) {
    return {
      roles, permissions, current: null, form: { name: '', permissions: [] }, errors: {}, busy: false,
      get grouped() { return this.permissions.reduce((g, p) => ((g[p.group] ||= []).push(p), g), {}); },
      select(r) { this.current = r; this.errors = {}; this.form = { name: r.name, permissions: [...r.permissions] }; },
      startNew() { this.select({ id: null, name: '', permissions: [], protected: false }); },
      async save() {
        this.busy = true; this.errors = {};
        try {
          const id = this.current.id;
          const r = await sharet.send(id ? 'PUT' : 'POST', id ? `${base}/${id}` : base, this.form);
          const saved = { ...this.current, id: r.role.id, name: r.role.name, label: r.role.name.replace(/_/g, ' ').replace(/^./, c => c.toUpperCase()), users: r.role.users_count, permissions: [...this.form.permissions], description: this.current.description || 'Custom role.' };
          this.roles = id ? this.roles.map(x => x.id === id ? saved : x) : [...this.roles, saved];
          this.current = saved;
        } catch (e) { this.errors = Object.fromEntries(Object.entries(e.errors || {}).map(([k, v]) => [k, v[0]])); }
        this.busy = false;
      },
      async destroy() {
        if (!confirm(`Delete the role "${this.current.label}"?`)) return;
        try { await sharet.send('DELETE', `${base}/${this.current.id}`); this.roles = this.roles.filter(x => x.id !== this.current.id); this.current = null; } catch (e) {}
      },
    };
  }
</script>
@endpush
