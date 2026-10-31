@php $select = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90'; @endphp
<x-ui.card title="Region & money" subtitle="Defaults for dates, times, amounts and lists.">
  <div class="grid gap-5 md:grid-cols-2">
    <x-ui.field label="Time zone" name="timezone" required>
      <select id="f-timezone" name="timezone" x-model="d.values.timezone" class="{{ $select }}"><template x-for="tz in d.timezones" :key="tz"><option :value="tz" x-text="tz"></option></template></select>
    </x-ui.field>
    <x-ui.field label="Currency" name="currency" required>
      <select id="f-currency" name="currency" x-model="d.values.currency" class="{{ $select }}"><template x-for="(label, code) in d.currencies" :key="code"><option :value="code" x-text="code + ' · ' + label"></option></template></select>
    </x-ui.field>
    <x-ui.field label="Currency symbol" name="currency_symbol" model="d.values.currency_symbol" required />
    <x-ui.field label="Rows per page" name="pagination_limit" type="number" min="5" max="100" model="d.values.pagination_limit" required />
    <x-ui.field label="Date format" name="date_format" required>
      <select id="f-date_format" name="date_format" x-model="d.values.date_format" class="{{ $select }}">
        <option value="d M Y">31 Oct 2026</option><option value="d/m/Y">31/10/2026</option><option value="Y-m-d">2026-10-31</option><option value="j F Y">31 October 2026</option>
      </select>
    </x-ui.field>
    <x-ui.field label="Time format" name="time_format">
      <select id="f-time_format" name="time_format" x-model="d.values.time_format" class="{{ $select }}"><option value="H:i">24-hour (14:30)</option><option value="g:i A">12-hour (2:30 PM)</option></select>
    </x-ui.field>
  </div>
</x-ui.card>
