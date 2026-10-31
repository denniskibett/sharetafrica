{{-- Account milestones. --}}
<x-ui.card title="Timeline">
  <ol class="relative space-y-4 border-l border-gray-200 pl-5 dark:border-gray-800">
    <template x-for="[what, when] in d.timeline" :key="what">
      <li class="relative"><span class="absolute -left-[26px] top-1 h-2.5 w-2.5 rounded-full bg-[#9CCB1F]"></span>
        <p class="text-sm font-medium text-gray-800 dark:text-white/90" x-text="what"></p><p class="text-xs text-gray-500" x-text="when"></p></li>
    </template>
  </ol>
</x-ui.card>
