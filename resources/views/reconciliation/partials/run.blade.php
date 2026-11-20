{{-- Pick a day and import its statement; the table refreshes in place. --}}
<div class="flex items-center gap-2" x-data="{ day: new Date().toISOString().slice(0, 10), busy: false }">
  <input type="date" x-model="day" :max="new Date().toISOString().slice(0, 10)" aria-label="Day to reconcile"
         class="h-10 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
  <button type="button" :disabled="busy"
          @click="busy = true; sharet.send('POST', '{{ route('ops.reconciliation.run') }}', { day }).then(() => window.dispatchEvent(new CustomEvent('table-reload'))).catch(() => {}).finally(() => busy = false)"
          class="inline-flex h-10 items-center gap-2 rounded-lg bg-gray-900 px-4 text-sm font-medium text-white hover:bg-gray-800 disabled:opacity-50 dark:bg-[#D4FF3D] dark:text-gray-900">
    <x-heroicon-o-arrow-path class="h-4 w-4" /> <span x-text="busy ? 'Matching…' : 'Run {{ $rail }}'"></span>
  </button>
</div>
