{{-- One transaction as a receipt in a right-hand panel (the mobile app's transaction screen): amount, transaction ID,
     recipient, account ID, bank, date and the money, then Download (PDF, styled like the emails) and Share. Open with
     sharet.panel('transaction', { url }). Loads PayController@show as JSON and follows it until it ends. --}}
<x-ui.slide-over name="transaction" title="Transaction" width="max-w-[560px]">
  <div x-data="{
         order: null, timer: null,
         async load(url) {
           clearTimeout(this.timer);
           try {
             const res = await fetch(url, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
             if (res.ok) this.order = (await res.json()).order;
           } catch (e) {}
           if (this.order && !this.order.final && open) this.timer = setTimeout(() => this.load(url), 2000);
         },
         /** A plain-text receipt, like an M-PESA message: the phone's share sheet, or copied where there is none. */
         async share() {
           const o = this.order;
           const text = ['Sharet receipt · ' + o.state.label, (o.in ? '+ KES ' + o.amount : '- KES ' + o.total)]
             .concat(Object.entries(o.receipt || {}).map(([k, v]) => k + ': ' + v), ['sharet.africa']).join('\n');
           try { if (navigator.share) return await navigator.share({ title: 'Sharet receipt', text }); } catch (e) { if (e.name === 'AbortError') return; }
           navigator.clipboard?.writeText(text); sharet.toast('Receipt copied: paste it anywhere');
         },
       }"
       @slide-over.window="if ($event.detail.name === 'transaction') { order = null; load($event.detail.payload.url) }">
    <p x-show="!order" class="py-10 text-center text-sm text-gray-500">Loading…</p>
    <div x-show="order" x-cloak class="space-y-6">
      <div class="flex flex-col items-center gap-2 pt-2 text-center">
        <span class="flex h-16 w-16 items-center justify-center rounded-full"
              :class="{ 'bg-app-in/15 text-app-in': order?.flow === 'in', 'bg-app-out/10 text-app-out': order?.flow === 'out', 'bg-app-tile text-app-muted': order?.flow === 'none', 'bg-app-wait/15 text-app-wait': order?.flow === 'pending' }">
          @include('pay.partials.icon', ['expr' => 'order', 'class' => 'h-7 w-7'])
        </span>
        <p class="mt-1 text-sm text-gray-500" x-text="order?.title"></p>
        <p class="text-xs text-gray-500"><span x-text="order?.from"></span> → <span x-text="order?.to"></span></p>
        <p class="text-[40px] font-bold leading-tight tabular-nums"
           :class="{ 'text-app-in': order?.flow === 'in', 'text-app-out': order?.flow === 'out', 'text-app-muted line-through': order?.flow === 'none', 'text-app-wait': order?.flow === 'pending' }" x-text="order?.signed.replace(/^([+−])/, '$1 KES ')"></p>
        <span :class="sharet.pill(order?.state.tone)" x-text="order?.state.label"></span>
      </div>

      {{-- Transaction ID first, large and copyable: the code people quote --}}
      <div class="flex items-center justify-between gap-3 rounded-2xl bg-gray-50 px-4 py-3 dark:bg-white/5">
        <div class="min-w-0">
          <p class="text-xs text-gray-500">Transaction ID</p>
          <p class="truncate text-xl font-semibold tracking-wider tabular-nums" x-text="order?.reference"></p>
        </div>
        <button type="button" @click="navigator.clipboard?.writeText(order.reference); sharet.toast('Transaction ID copied')" class="flex h-10 items-center gap-2 rounded-lg border border-gray-300 px-3 text-sm font-medium dark:border-gray-700" aria-label="Copy the transaction ID">
          <x-heroicon-o-document-duplicate class="h-5 w-5" /> Copy</button>
      </div>

      {{-- The receipt: the same lines as the PDF and the email (RampOrder::receipt) --}}
      <dl class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
          <template x-if="order?.receipt?.['Recipient']"><div class="flex items-center gap-3 py-3"><x-heroicon-o-user class="h-5 w-5 shrink-0 text-gray-400" /><dt class="text-gray-500">Recipient</dt><dd class="ml-auto truncate text-right font-medium" x-text="order.receipt['Recipient']"></dd></div></template>
          <template x-if="order?.receipt?.['Account ID']"><div class="flex items-center gap-3 py-3"><x-heroicon-o-identification class="h-5 w-5 shrink-0 text-gray-400" /><dt class="text-gray-500">Account ID</dt><dd class="ml-auto truncate text-right font-medium" x-text="order.receipt['Account ID']"></dd></div></template>
          <template x-if="order?.receipt?.['Bank']"><div class="flex items-center gap-3 py-3"><x-heroicon-o-building-library class="h-5 w-5 shrink-0 text-gray-400" /><dt class="text-gray-500">Bank</dt><dd class="ml-auto truncate text-right font-medium" x-text="order.receipt['Bank']"></dd></div></template>
          <template x-if="order?.receipt?.['From']"><div class="flex items-center gap-3 py-3"><x-heroicon-o-arrow-right-start-on-rectangle class="h-5 w-5 shrink-0 text-gray-400" /><dt class="text-gray-500">From</dt><dd class="ml-auto truncate text-right font-medium" x-text="order.receipt['From']"></dd></div></template>
          <template x-if="order?.receipt?.['Sharet reference']"><div class="flex items-center gap-3 py-3"><x-heroicon-o-hashtag class="h-5 w-5 shrink-0 text-gray-400" /><dt class="text-gray-500">Sharet reference</dt><dd class="ml-auto truncate text-right font-medium" x-text="order.receipt['Sharet reference']"></dd></div></template>
          <template x-if="order?.receipt?.['Date']"><div class="flex items-center gap-3 py-3"><x-heroicon-o-calendar class="h-5 w-5 shrink-0 text-gray-400" /><dt class="text-gray-500">Date</dt><dd class="ml-auto truncate text-right font-medium tabular-nums" x-text="order.receipt['Date']"></dd></div></template>
          <template x-if="order?.receipt?.['Amount']"><div class="flex items-center gap-3 py-3"><x-heroicon-o-banknotes class="h-5 w-5 shrink-0 text-gray-400" /><dt class="text-gray-500">Amount</dt><dd class="ml-auto truncate text-right font-medium tabular-nums" x-text="order.receipt['Amount']"></dd></div></template>
          <template x-if="order?.receipt?.['Fee and duty']"><div class="flex items-center gap-3 py-3"><x-heroicon-o-receipt-percent class="h-5 w-5 shrink-0 text-gray-400" /><dt class="text-gray-500">Fee and duty</dt><dd class="ml-auto truncate text-right font-medium tabular-nums" x-text="order.receipt['Fee and duty']"></dd></div></template>
          <template x-if="order?.receipt?.['Total']"><div class="flex items-center gap-3 py-3"><x-heroicon-o-wallet class="h-5 w-5 shrink-0 text-gray-400" /><dt class="text-gray-500">Total</dt><dd class="ml-auto truncate text-right font-medium tabular-nums" x-text="order.receipt['Total']"></dd></div></template>
          <template x-if="order?.receipt?.['Status']"><div class="flex items-center gap-3 py-3"><x-heroicon-o-check-badge class="h-5 w-5 shrink-0 text-gray-400" /><dt class="text-gray-500">Status</dt><dd class="ml-auto truncate text-right font-medium" x-text="order.receipt['Status']"></dd></div></template>
          <template x-if="order?.receipt?.['Note']"><div class="flex items-center gap-3 py-3"><x-heroicon-o-chat-bubble-bottom-center-text class="h-5 w-5 shrink-0 text-gray-400" /><dt class="text-gray-500">Note</dt><dd class="ml-auto truncate text-right font-medium" x-text="order.receipt['Note']"></dd></div></template>
      </dl>

      <div x-show="order?.final" class="grid grid-cols-2 gap-2">
        <a :href="order?.url + '/receipt'" class="flex h-12 items-center justify-center gap-2 rounded-xl border border-gray-300 text-sm font-semibold dark:border-gray-700">
          <x-heroicon-o-arrow-down-tray class="h-5 w-5" /> Download</a>
        <button type="button" @click="share()" class="flex h-12 items-center justify-center gap-2 rounded-xl bg-gray-900 text-sm font-semibold text-white dark:bg-[#D4FF3D] dark:text-gray-900">
          <x-heroicon-o-share class="h-5 w-5" /> Share</button>
      </div>

      <p x-show="order?.failure" class="rounded-xl bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-500/10 dark:text-red-300" x-text="order?.failure"></p>

      <div x-show="!order?.incoming && order?.timeline?.length">
        <p class="mb-3 text-sm font-semibold">Progress</p>
        @include('pay.partials.steps')
      </div>
      <p x-show="order?.sandbox" class="text-center text-xs text-amber-700 dark:text-amber-300">Test mode: simulated, no real money moved.</p>
    </div>
  </div>
</x-ui.slide-over>
