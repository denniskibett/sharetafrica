{{-- Where and from what this person signed up and sent KYC (App\Support\Footprint), newest first. --}}
<x-ui.card title="Sign-up and KYC footprint" subtitle="IP, country, browser and page for each sign-up and verification step.">
  <p x-show="!(d.footprint || []).length" class="text-sm text-gray-500">Nothing recorded yet. It starts with the next sign-up or verification step.</p>
  <ul class="divide-y divide-gray-100 dark:divide-gray-800" x-show="(d.footprint || []).length">
    <template x-for="(e, i) in (d.footprint || []).slice(0, 12)" :key="i">
      <li class="py-3 first:pt-0" x-data="{ more: false }">
        <div class="flex flex-wrap items-center justify-between gap-2">
          <span class="text-sm font-medium capitalize text-gray-800 dark:text-white/90" x-text="e.step.replaceAll('_', ' ')"></span>
          <span class="text-xs text-gray-500" x-text="e.when"></span>
        </div>
        <p class="mt-1 font-mono text-xs text-gray-600 dark:text-gray-400" x-text="[e.ip, e.country].filter(Boolean).join(' · ')"></p>
        <button type="button" class="mt-1 text-xs text-gray-500 underline" @click="more = !more" x-text="more ? 'Less' : 'More'"></button>
        <dl x-show="more" x-cloak class="mt-2 grid gap-1 text-xs">
          <template x-for="k in ['user_agent', 'languages', 'forwarded_for', 'path', 'referer', 'session']" :key="k">
            <div x-show="e[k]" class="grid grid-cols-[110px_minmax(0,1fr)] gap-2"><dt class="text-gray-500" x-text="k.replace('_', ' ')"></dt><dd class="break-all text-gray-700 dark:text-gray-300" x-text="e[k]"></dd></div>
          </template>
        </dl>
      </li>
    </template>
  </ul>
</x-ui.card>
