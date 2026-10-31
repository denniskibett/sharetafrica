{{-- Contact and verification details (ID and KRA numbers masked). --}}
<x-ui.card title="Details" subtitle="ID and KRA numbers are masked here.">
  <x-slot:actions><span :class="sharet.pill(d.level.key === 'standard' ? 'success' : d.level.key === 'basic' ? 'info' : 'warning')" x-text="d.level.label"></span></x-slot:actions>
  <dl class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
    <template x-for="[label, value] in d.details" :key="label">
      <div class="flex justify-between gap-4 py-2.5"><dt class="text-gray-500 dark:text-gray-400" x-text="label"></dt><dd class="text-right font-medium text-gray-800 dark:text-white/90" x-text="value || '—'"></dd></div>
    </template>
  </dl>
</x-ui.card>
