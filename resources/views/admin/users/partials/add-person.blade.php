{{-- Add person (slide-over). POST users.store; accounts made here are active straight away. --}}
<x-ui.slide-over name="add-person" title="Add person" subtitle="They can sign in straight away with this password; ask them to change it.">
  <form id="add-person-form" x-data="{ form: { name: '', email: '', phone: '', password: '', password_confirmation: '', role_id: '', company_id: '', status: 1 }, errors: {}, busy: false }"
        @submit.prevent="busy = true; errors = {};
          sharet.send('POST', '{{ route('users.store') }}', form)
            .then(() => { close(); load(); form = { name: '', email: '', phone: '', password: '', password_confirmation: '', role_id: '', company_id: '', status: 1 } })
            .catch(e => errors = Object.fromEntries(Object.entries(e.errors || {}).map(([k, v]) => [k, v[0]])))
            .finally(() => busy = false)"
        class="space-y-5">
    <x-ui.field label="Full name" name="name" required autocomplete="off" />
    <div class="grid gap-5 sm:grid-cols-2">
      <x-ui.field label="Email" name="email" type="email" required />
      <x-ui.field label="Phone" name="phone" type="tel" placeholder="+254 7…" />
    </div>
    <div class="grid gap-5 sm:grid-cols-2">
      <x-ui.field label="Password" name="password" type="password" required minlength="8" autocomplete="new-password" />
      <x-ui.field label="Confirm password" name="password_confirmation" type="password" required autocomplete="new-password" />
    </div>
    <x-ui.field label="Role" name="role_id" required>
      <select id="f-role_id" x-model="form.role_id" required class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm capitalize dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
        <option value="">Choose a role</option>
        <template x-for="r in extra.roles" :key="r.id"><option :value="r.id" x-text="r.name"></option></template>
      </select>
    </x-ui.field>
    <x-ui.field label="Company" name="company_id" help="Optional. Merchants and businesses belong to a company.">
      <select id="f-company_id" x-model="form.company_id" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
        <option value="">No company</option>
        <template x-for="c in extra.companies" :key="c.id"><option :value="c.id" x-text="c.name"></option></template>
      </select>
    </x-ui.field>
    <button type="submit" class="hidden"></button>
  </form>
  <x-slot:footer>
    <x-ui.button variant="secondary" @click="close()">Cancel</x-ui.button>
    <x-ui.button type="submit" form="add-person-form" icon="user-plus">Add person</x-ui.button>
  </x-slot:footer>
</x-ui.slide-over>
