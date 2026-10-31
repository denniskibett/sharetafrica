{{-- Pay anyone: M-PESA in, paid out to a phone, paybill, till or bank. Quote, confirm and progress all happen in place. --}}
@extends('layouts.app')
@section('title', 'Pay anyone')

@section('content')
<x-ui.page-header title="Pay anyone" subtitle="Pay a phone, paybill, till or bank account from your M-PESA. You see the full cost before you pay." />

@if (config('rails.default') === 'simulator')
  <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200">
    <strong>Test mode:</strong> payments are simulated, no real money moves. A number ending <code>0000</code> fails, ending <code>9999</code> answers slowly. Fees are placeholders until the partner tariff is agreed.
  </div>
@endif

<div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
  <div class="xl:col-span-5" x-data="payPage({ quoteUrl: '{{ route('pay.quote') }}', base: '{{ url('pay') }}' })">
    <x-ui.card>
      <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90" x-text="order ? 'Your payment' : 'New payment'"></h3>
      <div x-show="!order">@include('pay.partials.form')</div>
      <div x-show="order" x-cloak>
        @include('pay.partials.status')
        <button type="button" x-show="order?.final" @click="reset()" class="mt-4 h-11 w-full rounded-lg border border-gray-300 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Make another payment</button>
      </div>
    </x-ui.card>
  </div>

  <div class="xl:col-span-7" x-data="dataTable({ url: '{{ route('pay.index') }}', sort: '-date', perPage: 10 })">
    <x-ui.stats :cols="2" class="mb-6">
      <x-ui.stat label="Paid" value="stats.paid" icon="check-circle" tone="emerald" />
      <x-ui.stat label="Sent this way" value="stats.sent" format="raw" icon="banknotes" tone="violet" />
    </x-ui.stats>
    <x-ui.table title="Your payments" :searchable="false" :columns="[
        ['key' => 'date', 'label' => 'Date', 'type' => 'muted', 'sort' => 'date'],
        ['key' => 'recipient', 'label' => 'To'],
        ['key' => 'amount', 'label' => 'Amount', 'type' => 'code'],
        ['key' => 'pill', 'label' => 'Status', 'type' => 'pill'],
    ]">
      <x-slot:actions>
        <x-ui.row-menu>
          <x-ui.row-menu-item icon="eye" x-bind:href="row.url">Open</x-ui.row-menu-item>
        </x-ui.row-menu>
      </x-slot:actions>
      <x-slot:empty>No payments yet.</x-slot:empty>
    </x-ui.table>
  </div>
</div>

<script>
  function payPage({ quoteUrl, base }) {
    return {
      type: 'phone', form: { phone: '', paybill: '', till: '', account: '', bank: '', amount: '', note: '' },
      errors: {}, quote: null, order: null, busy: false, timer: null,
      async getQuote() {
        this.busy = true; this.errors = {};
        try { this.quote = (await sharet.send('POST', quoteUrl, { ...this.form, type: this.type })).order; }
        catch (e) { this.errors = e.errors || {}; }
        this.busy = false;
      },
      async confirm() {
        this.busy = true; this.errors = {};
        try {
          this.order = (await sharet.send('POST', `${base}/${this.quote.uuid}/confirm`)).order;
          this.quote = null; this.follow();
        } catch (e) { this.errors = e.errors || {}; }
        this.busy = false;
      },
      follow() {
        clearTimeout(this.timer);
        window.dispatchEvent(new CustomEvent('table-reload'));
        if (!this.order || this.order.final) return;
        this.timer = setTimeout(async () => {
          try { this.order = (await getJsonOrder(this.order.url)).order; } catch (e) {}
          this.follow();
        }, 2000);
      },
      reset() { clearTimeout(this.timer); this.order = null; this.quote = null; this.form.amount = ''; this.form.note = ''; },
    };
  }
  async function getJsonOrder(url) {
    const res = await fetch(url, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
    if (!res.ok) throw new Error(res.status);
    return res.json();
  }
</script>
@endsection
