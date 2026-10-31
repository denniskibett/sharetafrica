{{-- Add / edit a group company (slide-over, multipart so the logo uploads too; paste works for the logo). --}}
<x-ui.slide-over name="group-company" title="Company" width="max-w-2xl">
  <form id="group-company-form" class="space-y-5"
        x-data="{ form: {}, errors: {}, logo: null }"
        x-effect="form = Object.assign({ name: '', slug: '', sector: '', tagline: '', description: '', services: '', served: '', url: '', email: '', phone: '', sort: '', published: true }, payload.form || {}); logo = payload.form?.logo || null; errors = {}"
        @submit.prevent="const data = new FormData($el); data.set('published', form.published ? 1 : 0);
          sharet.send('POST', payload.id ? '{{ url('admin/companies') }}/' + payload.id : '{{ route('admin.group-companies.store') }}', data)
            .then(() => { close(); load() })
            .catch(e => errors = Object.fromEntries(Object.entries(e.errors || {}).map(([k, v]) => [k, v[0]])))">
    <div class="flex items-center gap-4">
      <span class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gray-100 dark:bg-white/5">
        <img x-show="logo" :src="logo" alt="" class="h-full w-full object-contain">
        <x-heroicon-o-building-office-2 x-show="!logo" class="h-7 w-7 text-gray-400" />
      </span>
      <label class="inline-flex h-10 cursor-pointer items-center gap-2 rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
        <x-heroicon-o-arrow-up-tray class="h-5 w-5" /> Logo
        <input type="file" name="logo" accept="image/png,image/jpeg,image/webp,image/svg+xml" class="sr-only" @change="logo = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : logo">
      </label>
      <p class="text-xs text-gray-500">Optional. PNG, SVG or WebP. You can also paste one.</p>
    </div>
    <div class="grid gap-5 sm:grid-cols-2">
      <x-ui.field label="Name" name="name" required />
      <x-ui.field label="Sector" name="sector" placeholder="Property" />
      <x-ui.field label="One-line tagline" name="tagline" class="sm:col-span-2" />
      <x-ui.field label="Website" name="url" type="url" placeholder="https://" />
      <x-ui.field label="Page address" name="slug" help="sharet.africa/companies/… (from the name if empty)" />
    </div>
    <x-ui.field label="What it does" name="description">
      <textarea id="f-description" name="description" x-model="form.description" rows="4" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></textarea>
    </x-ui.field>
    <x-ui.field label="Services" name="services" help="One per line.">
      <textarea id="f-services" name="services" x-model="form.services" rows="5" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></textarea>
    </x-ui.field>
    <div class="grid gap-5 sm:grid-cols-2">
      <x-ui.field label="Where it operates" name="served" placeholder="Kenya (Nairobi)" />
      <x-ui.field label="Order" name="sort" type="number" min="0" />
      <x-ui.field label="Email" name="email" type="email" />
      <x-ui.field label="Phone" name="phone" type="tel" />
    </div>
    <label class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300">
      <input type="checkbox" x-model="form.published" class="h-4 w-4 rounded border-gray-300 text-gray-900"> Show on the website
    </label>
  </form>
  <x-slot:footer>
    <x-ui.button variant="secondary" @click="close()">Cancel</x-ui.button>
    <x-ui.button type="submit" form="group-company-form" icon="check">Save</x-ui.button>
  </x-slot:footer>
</x-ui.slide-over>
