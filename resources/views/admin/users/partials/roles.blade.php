{{-- Change roles (slide-over). --}}
<x-ui.slide-over name="user-roles" title="Change roles" subtitle="Roles decide what this person can open in the dashboard.">
  <form id="user-roles-form" x-data="{ picked: [] }" x-effect="open && (picked = (payload.roles || []).map(r => r.id))"
        @submit.prevent="sharet.send('PUT', '{{ route('users.roles', $user) }}', { roles: picked }).then(() => { close(); load() }).catch(() => {})" class="space-y-2">
    <template x-for="r in d.all_roles" :key="r.id">
      <label class="flex cursor-pointer items-center justify-between rounded-xl border border-gray-200 px-4 py-3 text-sm capitalize hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-white/5">
        <span x-text="r.name"></span>
        <input type="checkbox" :value="r.id" x-model.number="picked" class="h-4 w-4 rounded border-gray-300 text-gray-900">
      </label>
    </template>
  </form>
  <x-slot:footer>
    <x-ui.button variant="secondary" @click="close()">Cancel</x-ui.button>
    <x-ui.button type="submit" form="user-roles-form" icon="check">Save roles</x-ui.button>
  </x-slot:footer>
</x-ui.slide-over>
