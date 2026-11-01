{{-- Personal details + photo (multipart PATCH /profile). --}}
<x-ui.card title="Personal details" subtitle="Use your name as it appears on your ID. Your phone number is your M-Pesa / mobile money line.">
  <x-slot:actions><x-ui.button variant="secondary" icon="user-circle" href="{{ route('profile.show') }}">View profile</x-ui.button></x-slot:actions>
  <form @submit.prevent="const f = new FormData($el); f.set('_method', 'PATCH'); submit('POST', '{{ route('profile.update') }}', f)" class="space-y-6">
    <div class="flex flex-wrap items-center gap-5">
      <span class="flex h-20 w-20 items-center justify-center overflow-hidden rounded-full bg-[#050507] text-[#D4FF3D] dark:bg-[#D4FF3D] dark:text-[#050507] text-2xl font-bold">
        <img x-show="data.user.avatar" :src="data.user.avatar" alt="" class="h-full w-full object-cover">
        <span x-show="!data.user.avatar" x-text="data.user.initials"></span>
      </span>
      <div class="flex flex-wrap gap-2">
        <label class="inline-flex h-10 cursor-pointer items-center gap-2 rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
          <x-heroicon-o-camera class="h-5 w-5" /> Change photo
          <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="sr-only" @change="data.user.avatar = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : data.user.avatar">
        </label>
        <x-ui.button variant="secondary" x-show="data.user.avatar" @click="sharet.send('DELETE', '{{ route('profile.delete-avatar') }}').then(() => load())">Remove</x-ui.button>
      </div>
      <p class="w-full text-xs text-gray-500 dark:text-gray-400">JPG, PNG or WebP, up to 2 MB. You can also paste a photo (⌘V / Ctrl+V).</p>
      <p x-show="errors.avatar" x-text="errors.avatar" class="w-full text-xs text-red-600"></p>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
      <x-ui.field label="Full legal name" name="name" model="data.user.name" required autocomplete="name" />
      <x-ui.field label="Email" name="email" type="email" model="data.user.email" required autocomplete="email" help="Changing it asks you to verify the new address." />
      <x-ui.field label="Mobile number" name="phone" type="tel" model="data.user.phone" placeholder="+254 712 345 678" autocomplete="tel" help="Needed for basic verification." />
      <x-ui.field label="Country" name="country" model="data.user.country" placeholder="Kenya" autocomplete="country-name" />
      <x-ui.field label="Town / city" name="city" model="data.user.city" placeholder="Nairobi" autocomplete="address-level2" />
    </div>
    <x-ui.field label="About you" name="bio">
      <textarea id="f-bio" name="bio" x-model="data.user.bio" rows="3" maxlength="500" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></textarea>
    </x-ui.field>
    <div class="flex justify-end"><x-ui.button type="submit" icon="check" x-bind:disabled="busy">Save details</x-ui.button></div>
  </form>
</x-ui.card>
