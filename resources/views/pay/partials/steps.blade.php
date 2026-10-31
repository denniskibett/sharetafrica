{{-- Progress steps with a standard icon each, evenly spaced (needs Alpine `order`, RampOrder::present()). --}}
{{-- Equal rows, so every step sits the same distance apart however long its text --}}
<ol class="grid auto-rows-fr">
  <template x-for="(e, i) in order?.timeline || []" :key="i">
    <li class="relative flex min-h-[64px] gap-3 pb-4">
      <span x-show="i < order.timeline.length - 1" class="absolute bottom-0 left-[17px] top-9 w-px bg-gray-200 dark:bg-gray-700"></span>
      <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full"
            :class="e.icon === 'x-circle' ? 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-300'
              : (i === order.timeline.length - 1 ? 'bg-[#D4FF3D] text-[#050507]' : 'bg-gray-100 text-gray-500 dark:bg-white/5 dark:text-gray-400')">
        @foreach (['document-text', 'device-phone-mobile', 'paper-airplane', 'check-circle', 'arrow-uturn-left', 'x-circle', 'clock'] as $name)
          <x-dynamic-component :component="'heroicon-o-'.$name" x-show="e.icon === '{{ $name }}'" class="h-[18px] w-[18px]" />
        @endforeach
      </span>
      <span class="min-w-0 pt-1 text-sm"><span class="block text-gray-700 dark:text-gray-300" x-text="e.text"></span><span class="app-num text-xs text-gray-400" x-text="e.at"></span></span>
    </li>
  </template>
</ol>
