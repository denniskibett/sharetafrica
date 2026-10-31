{{-- Add / edit a team member (slide-over). Sent as multipart so the photo uploads with the form. --}}
<x-ui.slide-over name="member" title="Team member">
  <form id="member-form" class="space-y-5"
        x-data="{ form: {}, errors: {}, busy: false, preview: null }"
        x-effect="form = { name: payload.name || '', role: payload.role || '', bio: payload.bio || '', linkedin: payload.linkedin || '', sort: payload.sort ?? '', published: payload.published ?? true }; preview = payload.person?.avatar || null; errors = {}"
        @submit.prevent="busy = true; errors = {};
          const data = new FormData($el); data.set('published', form.published ? 1 : 0);
          sharet.send('POST', payload.id ? '{{ url('admin/team') }}/' + payload.id : '{{ route('admin.team.store') }}', data)
            .then(() => { close(); load() })
            .catch(e => errors = Object.fromEntries(Object.entries(e.errors || {}).map(([k, v]) => [k, v[0]])))
            .finally(() => busy = false)">
    <div class="flex items-center gap-5">
      <span class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-gray-100 text-2xl font-semibold text-gray-500 dark:bg-white/5">
        <img x-show="preview" :src="preview" alt="" class="h-full w-full object-cover">
        <x-heroicon-o-camera x-show="!preview" class="h-8 w-8" />
      </span>
      <div>
        <label class="inline-flex h-10 cursor-pointer items-center gap-2 rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
          <x-heroicon-o-arrow-up-tray class="h-5 w-5" /> Upload photo
          <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="sr-only" @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : preview">
        </label>
        <p class="mt-2 text-xs text-gray-500">JPG, PNG or WebP, up to 4 MB. Square, head and shoulders.</p>
        <p x-show="errors.photo" x-text="errors.photo" class="mt-1 text-xs text-red-600"></p>
      </div>
    </div>
    <x-ui.field label="Full name" name="name" required />
    <x-ui.field label="Role" name="role" required placeholder="Co-founder · CEO" />
    <x-ui.field label="Short bio" name="bio">
      <textarea id="f-bio" name="bio" x-model="form.bio" rows="4" maxlength="600" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></textarea>
    </x-ui.field>
    <div class="grid gap-5 sm:grid-cols-[1fr_120px]">
      <x-ui.field label="LinkedIn URL" name="linkedin" type="url" placeholder="https://www.linkedin.com/in/…" />
      <x-ui.field label="Order" name="sort" type="number" min="0" />
    </div>
    <label class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300">
      <input type="checkbox" x-model="form.published" class="h-4 w-4 rounded border-gray-300 text-gray-900"> Show on the website
    </label>
  </form>
  <x-slot:footer>
    <x-ui.button variant="secondary" @click="close()">Cancel</x-ui.button>
    <x-ui.button type="submit" form="member-form" icon="check">Save</x-ui.button>
  </x-slot:footer>
</x-ui.slide-over>
