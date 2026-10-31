<x-ui.card title="Maintenance mode" subtitle="Shows visitors a maintenance notice. Signed-in admins still get in.">
  <div class="flex flex-wrap items-center justify-between gap-4">
    <span :class="sharet.pill(d.maintenance ? 'warning' : 'success')" x-text="d.maintenance ? 'On: visitors see the maintenance notice' : 'Off: the site is open'"></span>
    <x-ui.button variant="secondary" icon="wrench-screwdriver"
        @click="sharet.confirm({ title: d.maintenance ? 'Turn maintenance off?' : 'Turn maintenance on?', message: d.maintenance ? 'Visitors can use the site again.' : 'Visitors will see a maintenance notice until you turn it off.', tone: d.maintenance ? 'info' : 'warning', confirmText: d.maintenance ? 'Turn off' : 'Turn on' })
          .then(ok => ok && sharet.send('POST', '{{ route('system.toggle-maintenance') }}').then(r => d.maintenance = r.maintenance).catch(() => {}))"
        x-text="d.maintenance ? 'Turn off' : 'Turn on'"></x-ui.button>
  </div>
</x-ui.card>

<x-ui.card title="Custom code" subtitle="Added to every page. Only for small fixes; anything larger belongs in the codebase." class="mt-6">
  <div class="space-y-5">
    <x-ui.field label="Custom CSS" name="custom_css">
      <textarea id="f-custom_css" name="custom_css" x-model="d.values.custom_css" rows="5" spellcheck="false" class="w-full rounded-lg border border-gray-300 bg-gray-950 px-4 py-3 font-mono text-xs text-gray-100 dark:border-gray-700"></textarea>
    </x-ui.field>
    <x-ui.field label="Custom JavaScript" name="custom_js">
      <textarea id="f-custom_js" name="custom_js" x-model="d.values.custom_js" rows="5" spellcheck="false" class="w-full rounded-lg border border-gray-300 bg-gray-950 px-4 py-3 font-mono text-xs text-gray-100 dark:border-gray-700"></textarea>
    </x-ui.field>
  </div>
</x-ui.card>
