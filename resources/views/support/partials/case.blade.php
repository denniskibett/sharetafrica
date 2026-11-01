{{-- One case in the panel: what happened, the payment, owner and SLA; assign, then close with what was done. --}}
<x-ui.slide-over name="case" title="Case" x-text="payload.number">
  <div x-data="{ note: '' }" class="space-y-5 text-sm">
    <div class="flex flex-wrap items-center gap-2">
      <span :class="sharet.pill(payload.pill?.tone)" x-text="payload.pill?.label"></span>
      <span class="rounded-full bg-gray-100 px-2.5 py-0.5 font-mono text-xs dark:bg-white/5" x-text="payload.reason"></span>
      <span class="text-gray-500" x-text="payload.channel + ' · opened ' + payload.opened"></span>
    </div>
    <p class="text-base font-medium text-gray-900 dark:text-white" x-text="payload.summary"></p>

    <dl class="divide-y divide-gray-100 dark:divide-gray-800">
      <div class="flex justify-between py-2"><dt class="text-gray-500">Customer</dt><dd x-text="payload.customer?.name || '—'"></dd></div>
      <div class="flex justify-between py-2"><dt class="text-gray-500">M-PESA number</dt><dd class="tabular-nums" x-text="payload.phone || '—'"></dd></div>
      <template x-if="payload.order">
        <div class="flex justify-between py-2"><dt class="text-gray-500">Payment</dt><dd class="text-right"><span x-text="payload.order.title"></span><br><span class="tabular-nums text-gray-500" x-text="'KES ' + payload.order.amount + ' · ' + payload.order.state + ' · ' + payload.order.ref"></span></dd></div>
      </template>
      <div class="flex justify-between py-2"><dt class="text-gray-500">Owner</dt><dd x-text="payload.owner"></dd></div>
      <div class="flex justify-between py-2" x-show="payload.is_open"><dt class="text-gray-500">SLA left</dt><dd class="tabular-nums" x-text="payload.sla"></dd></div>
    </dl>

    <template x-if="!payload.is_open">
      <p class="rounded-lg bg-emerald-50 p-3 text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-200"><span x-text="payload.close_note"></span><br><span class="text-xs" x-text="'Closed by ' + payload.closed"></span></p>
    </template>

    <div x-show="payload.is_open" class="space-y-3">
      <x-ui.button variant="secondary" icon="user-plus" x-show="!payload.mine"
                   @click="support.act('{{ url('ops/support') }}/' + payload.id + '/assign').then(r => Object.assign(payload, r.case))">Assign to me</x-ui.button>
      <form @submit.prevent="support.act('{{ url('ops/support') }}/' + payload.id + '/close', { note }).then(r => { note = ''; Object.assign(payload, r.case) })" class="space-y-2">
        <label for="case-note" class="block font-medium text-gray-700 dark:text-gray-300">What was done for the customer</label>
        <textarea id="case-note" x-model="note" rows="4" class="w-full rounded-lg border border-gray-300 bg-transparent p-3 dark:border-gray-700"
                  placeholder="Called them: the KES 10.00 is in their wallet, M-PESA UK1PE00E4V."></textarea>
        <x-ui.button type="submit" icon="check">Close case</x-ui.button>
      </form>
    </div>
  </div>
</x-ui.slide-over>
