<x-ui.card title="Search & sharing" subtitle="Fallbacks for pages that don't set their own. Each public page already has its own title and description.">
  <div class="space-y-5">
    <x-ui.field label="Default description" name="meta_description">
      <textarea id="f-meta_description" name="meta_description" x-model="d.values.meta_description" rows="3" maxlength="300" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></textarea>
    </x-ui.field>
    <x-ui.field label="Keywords" name="meta_keywords" model="d.values.meta_keywords" help="Comma separated. Search engines mostly ignore these; kept for older tools." />
  </div>
</x-ui.card>
