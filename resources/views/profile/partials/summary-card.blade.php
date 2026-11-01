{{-- Profile header: photo, name, lane, contact details and Edit profile. Reads `data` from profile(). --}}
<x-ui.card class="mb-6">
  <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
    <span class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-full bg-[#050507] text-[#D4FF3D] dark:bg-[#D4FF3D] dark:text-[#050507] text-2xl font-bold">
      <img x-show="data.user.avatar" :src="data.user.avatar" alt="" class="h-full w-full object-cover">
      <span x-show="!data.user.avatar" x-text="data.user.initials"></span>
    </span>
    <div class="min-w-0 flex-1">
      <h2 class="text-xl font-semibold text-gray-900 dark:text-white" x-text="data.user.name"></h2>
      <div class="mt-1 flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
        <span x-text="data.user.lane"></span>
        <span x-show="data.user.company">· <span x-text="data.user.company"></span></span>
        <span :class="sharet.pill(data.user.status?.tone)" x-text="data.user.status?.label"></span>
      </div>
      <p class="mt-2 max-w-2xl text-sm text-gray-600 dark:text-gray-300" x-show="data.user.bio" x-text="data.user.bio"></p>
    </div>
    <dl class="grid gap-x-8 gap-y-2 text-sm sm:grid-cols-2 sm:text-right">
      <div><dt class="text-xs text-gray-500">Email</dt><dd class="font-medium text-gray-800 dark:text-white/90" x-text="data.user.email"></dd></div>
      <div><dt class="text-xs text-gray-500">Phone</dt><dd class="font-medium text-gray-800 dark:text-white/90" x-text="data.user.phone || 'Not added'"></dd></div>
    </dl>
    <x-ui.button icon="pencil-square" href="{{ route('profile.edit') }}" class="shrink-0">Edit profile</x-ui.button>
  </div>
</x-ui.card>
