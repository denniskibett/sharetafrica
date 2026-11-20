{{-- One payment, following itself until it ends (polls PayController@show as JSON; no reloads). --}}
@extends('layouts.app')
@section('title', 'Payment')

@section('content')
<x-ui.page-header title="Payment" :subtitle="$order['date'].' · '.$order['recipient']" />
<p class="-mt-4 mb-6 text-sm"><a href="{{ route('pay.index') }}" class="text-gray-500 hover:underline">← All transactions</a></p>

<div class="max-w-xl" x-data="{
    order: @js($order), timer: null,
    init() { this.follow(); },
    follow() {
      if (this.order.final) return;
      this.timer = setTimeout(async () => {
        try {
          const res = await fetch(this.order.url, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
          if (res.ok) this.order = (await res.json()).order;
        } catch (e) {}
        this.follow();
      }, 2000);
    },
  }">
  <x-ui.card>
    @include('pay.partials.status')
    <p x-show="order.sandbox" class="mt-4 text-xs text-amber-700 dark:text-amber-300">Test mode: simulated payment, no real money moved.</p>
  </x-ui.card>
</div>
@endsection
