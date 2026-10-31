<x-ui.card title="Brand colours" subtitle="Primary is the highlight (lime); text on it is always dark. Secondary is the near-black used for buttons and the dark theme.">
  <div class="grid gap-5 md:grid-cols-2">
    @foreach (['primary_color' => 'Primary', 'secondary_color' => 'Secondary'] as $key => $label)
      <div>
        <label for="f-{{ $key }}" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</label>
        <div class="flex items-center gap-3">
          <input type="color" x-model="d.values.{{ $key }}" class="h-11 w-14 cursor-pointer rounded-lg border border-gray-300 bg-transparent p-1 dark:border-gray-700" aria-label="{{ $label }} colour picker">
          <input id="f-{{ $key }}" name="{{ $key }}" x-model="d.values.{{ $key }}" maxlength="7" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 font-mono text-sm uppercase dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
        </div>
        <p x-show="errors.{{ $key }}" x-text="errors.{{ $key }}" class="mt-1 text-xs text-red-600"></p>
      </div>
    @endforeach
  </div>
  <div class="mt-5 flex flex-wrap items-center gap-3 rounded-xl border border-gray-200 p-4 dark:border-gray-800">
    <span class="rounded-lg px-4 py-2 text-sm font-semibold text-gray-900" :style="`background:${d.values.primary_color}`">Primary button</span>
    <span class="rounded-lg px-4 py-2 text-sm font-semibold" :style="`background:${d.values.secondary_color};color:${d.values.primary_color}`">Secondary</span>
    <span class="text-xs text-gray-500">Preview</span>
  </div>
</x-ui.card>

<x-ui.card title="Logos" subtitle="The dashboard and website use the Two Lanes brand kit (public/images/brand). These uploads are used by older pages and exports." class="mt-6">
  <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach (['logo' => 'Logo (light)', 'logo_dark' => 'Logo (dark)', 'logo_icon' => 'Icon', 'favicon' => 'Favicon'] as $key => $label)
      <label class="flex cursor-pointer flex-col gap-2 rounded-xl border border-gray-200 p-4 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-white/5">
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</span>
        <span class="flex h-16 items-center justify-center rounded-lg {{ $key === 'logo_dark' ? 'bg-gray-900' : 'bg-gray-50 dark:bg-white/5' }}">
          <img x-show="d.files.{{ $key }}" :src="d.files.{{ $key }}" alt="" class="max-h-12 max-w-full">
        </span>
        <span class="text-xs text-gray-500">Click or paste to replace · SVG or PNG</span>
        <input type="file" name="{{ $key }}" accept="image/svg+xml,image/png,image/webp,image/x-icon" class="sr-only" @change="d.files.{{ $key }} = URL.createObjectURL($event.target.files[0])">
      </label>
    @endforeach
  </div>
</x-ui.card>
