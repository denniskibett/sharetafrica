{{-- Person header: photo, name, lane, status, roles, and the ⋯ actions. --}}
<x-ui.card class="mb-6">
  <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
    <span class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full bg-gray-900 text-2xl font-bold text-[#D4FF3D]">
      <img x-show="d.person.avatar" :src="d.person.avatar" alt="" class="h-full w-full object-cover">
      <span x-show="!d.person.avatar" x-text="d.person.initials"></span>
    </span>
    <div class="min-w-0 flex-1">
      <h2 class="text-xl font-semibold text-gray-900 dark:text-white" x-text="d.person.name"></h2>
      <p class="text-sm text-gray-500 dark:text-gray-400" x-text="d.person.email"></p>
      <div class="mt-2 flex flex-wrap items-center gap-2 text-sm">
        <span :class="sharet.pill(d.status?.tone)" x-text="d.status?.label"></span>
        <span :class="sharet.pill('neutral')" x-text="d.lane"></span>
        <template x-for="r in d.roles" :key="r.id"><span :class="sharet.pill('dark')" class="capitalize" x-text="r.name"></span></template>
        <span x-show="d.company" class="text-gray-500">· <span x-text="d.company"></span></span>
      </div>
    </div>
    <div class="flex items-center gap-2">
      @role('admin|super_admin')
        <x-ui.button x-show="d.waiting" icon="paper-airplane"
            @click="sharet.confirm({ title: 'Send an invite?', message: 'We email them a link to finish setting up.', tone: 'info', confirmText: 'Send invite', details: [['Name', d.person.name], ['Email', d.person.email]] })
              .then(ok => ok && sharet.send('POST', '{{ url('admin/waiting-list') }}/{{ $user->id }}/invite').then(() => load()).catch(() => {}))">Invite</x-ui.button>
      @endrole
      <x-ui.row-menu>
        <x-ui.row-menu-item icon="envelope" x-bind:href="'mailto:' + d.person.email">Email</x-ui.row-menu-item>
        <x-ui.row-menu-item icon="phone" x-show="d.person.phone" x-bind:href="'tel:' + d.person.phone">Call</x-ui.row-menu-item>
        @role('admin|super_admin')
          <x-ui.row-menu-item icon="shield-check" @click="sharet.panel('user-roles', d)">Change roles</x-ui.row-menu-item>
          <x-ui.row-menu-item icon="trash" danger x-show="!d.self"
              @click="sharet.confirm({ title: 'Remove this person?', message: 'They lose access straight away. Their records stay for audit.', tone: 'error', confirmText: 'Remove', details: [['Name', d.person.name], ['Email', d.person.email], ['Lane', d.lane]] })
                .then(ok => ok && sharet.send('DELETE', '{{ route('users.destroy', $user) }}').then(() => location.href = '{{ route('users.index') }}').catch(() => {}))">Remove</x-ui.row-menu-item>
        @endrole
      </x-ui.row-menu>
    </div>
  </div>
</x-ui.card>
