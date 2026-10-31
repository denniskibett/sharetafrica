{{-- Table filters; options come from the JSON `extra` block. --}}
@php $select = 'h-10 rounded-lg border border-gray-300 bg-transparent pl-3 pr-9 text-sm text-gray-700 shadow-theme-xs dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300'; @endphp
<select x-model="filters.lane" aria-label="Lane" class="{{ $select }}">
  <option value="">All lanes</option>
  <template x-for="(label, key) in extra.lanes" :key="key"><option :value="key" x-text="label"></option></template>
</select>
<select x-model="filters.status" aria-label="Status" class="{{ $select }}">
  <option value="">Any status</option>
  <template x-for="(label, key) in extra.statuses" :key="key"><option :value="key" x-text="label"></option></template>
</select>
<select x-model="filters.role" aria-label="Role" class="{{ $select }} capitalize">
  <option value="">Any role</option>
  <template x-for="r in extra.roles" :key="r.id"><option :value="r.key" x-text="r.name"></option></template>
</select>
