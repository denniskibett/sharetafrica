{{-- Verification photos for review (private files, staff only). Click one to open it full size. --}}
<x-ui.card title="Verification photos" subtitle="ID, selfies and signature. Check the face matches the ID and the details are readable.">
  <x-slot:actions><span :class="sharet.pill(d.level.standard?.documents?.[0] === d.level.standard?.documents?.[1] ? 'success' : 'warning')" x-text="(d.level.standard?.documents?.[0] ?? 0) + ' of ' + (d.level.standard?.documents?.[1] ?? 0)"></span></x-slot:actions>
  <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
    <template x-for="doc in d.documents" :key="doc.type">
      <a :href="doc.url || null" target="_blank" rel="noopener" class="group block overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
        <div class="flex aspect-[4/3] items-center justify-center bg-gray-50 dark:bg-white/[0.03]">
          <img x-show="doc.url" :src="doc.url" :alt="doc.label" loading="lazy" class="h-full w-full object-cover transition group-hover:scale-[1.02]">
          <span x-show="!doc.url" class="text-xs text-gray-400">Not added</span>
        </div>
        <p class="px-3 py-2 text-xs font-medium text-gray-700 dark:text-gray-300" x-text="doc.label"></p>
      </a>
    </template>
  </div>
</x-ui.card>
