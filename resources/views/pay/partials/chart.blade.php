{{-- Money in (green) and out (red) per day, above the transactions. Light on phone data: the chart library (~500 KB)
     only loads when the chart scrolls into view, and nothing shows until the person has a transaction (`rows`).
     Phones show it after the transactions (order-3), wider screens before them (pages give the list order-2). --}}
<div x-data="{ seen: false }" x-intersect.once.margin.200px="seen = true" x-show="rows.length" x-cloak class="order-3 min-h-[1px] lg:order-1">
  <template x-if="seen">
    <x-ui.chart :url="route('pay.chart')" title="Money in and out" subtitle="Per day, in KES" :height="240"
                :ranges="[7 => '7 days', 30 => '30 days', 90 => '90 days']" :days="30" class="!border-app-line !bg-app-panel" />
  </template>
</div>
