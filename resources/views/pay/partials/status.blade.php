{{-- One payment's live state. Needs an Alpine `order` (RampOrder::present()) in scope. --}}
<div class="space-y-4" x-show="order" x-cloak>
  <div class="flex items-start justify-between gap-3">
    <div class="min-w-0">
      <p class="text-sm text-gray-500 dark:text-gray-400">To</p>
      <p class="truncate font-medium text-gray-900 dark:text-white" x-text="order?.recipient"></p>
      <p class="text-sm text-gray-500 dark:text-gray-400" x-show="order?.note" x-text="order?.note"></p>
    </div>
    <span class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium"
          :class="{
            'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300': order?.state.tone === 'success',
            'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300': order?.state.tone === 'warning',
            'bg-sky-50 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300': order?.state.tone === 'info',
            'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-300': order?.state.tone === 'error',
            'bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-gray-300': order?.state.tone === 'neutral',
          }" x-text="order?.state.label"></span>
  </div>

  <dl class="divide-y divide-gray-100 rounded-2xl bg-gray-50 px-4 text-sm dark:divide-gray-800 dark:bg-white/5">
    <div class="flex justify-between py-2" x-show="!order?.top_up"><dt class="text-gray-500">They receive</dt><dd class="tabular-nums" x-text="'KES ' + order?.amount"></dd></div>
    <div class="flex justify-between py-2" x-show="!order?.top_up"><dt class="text-gray-500">Fee (M-PESA charge + Sharet)</dt><dd class="tabular-nums" x-text="'KES ' + order?.fee"></dd></div>
    <div class="flex justify-between py-2" x-show="!order?.top_up"><dt class="text-gray-500">Excise duty (15% of the Sharet part)</dt><dd class="tabular-nums" x-text="'KES ' + order?.excise"></dd></div>
    <div class="flex justify-between py-2" x-show="order?.rounding !== '0.00'"><dt class="text-gray-500">Rounding to whole shillings</dt><dd class="tabular-nums" x-text="'KES ' + order?.rounding"></dd></div>
    <div class="flex justify-between py-2 font-semibold text-gray-900 dark:text-white">
      <dt x-text="order?.top_up ? 'You add from M-PESA' : (order?.funding === 'wallet' ? 'You pay from your wallet' : 'You pay from M-PESA')"></dt>
      <dd class="tabular-nums" x-text="'KES ' + order?.total"></dd>
    </div>
  </dl>

  <p x-show="order?.failure" class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-500/10 dark:text-red-300" x-text="order?.failure"></p>

  <div x-show="order?.timeline?.length">@include('pay.partials.steps')</div>

  <p x-show="order && !order.final && order.status !== 'quoted'" class="flex items-center gap-2 text-xs text-gray-500">
    <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-500"></span> Updating by itself. You can leave this page; we will text you the result.
  </p>
</div>
