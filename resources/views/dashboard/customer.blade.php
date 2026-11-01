{{-- Customer home, a personal app: the wallet (send, receive, withdraw in the money panel), a verify prompt until the
     ID and selfies are in, and the latest transactions. ?panel=send|receive|withdraw (&to=@name) opens the panel. --}}
@extends('layouts.app')
@section('title', 'Home')

@php $verified = in_array($user->kyc_level, ['basic', 'standard'], true); @endphp

@section('content')
<div class="flex flex-col gap-6"
     x-data="{
        wallet: @js($wallet), rows: [], stats: {}, loading: true,
        async load() {
          const r = await (await fetch('{{ route('pay.index') }}?per_page=10&sort=-date', { headers: { Accept: 'application/json' } })).json();
          this.rows = r.data; this.stats = r.stats; this.wallet = r.stats.wallet; this.loading = false;
        },
     }"
     x-init="load()"
     @wallet-changed.window="load()">

  @unless ($verified)
    <a href="{{ route('profile.edit') }}#verification" class="flex items-center gap-4 rounded-2xl border border-app-accent/40 bg-app-panel p-4 hover:border-app-accent">
      <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-app-lime text-[#050507]"><x-heroicon-o-identification class="h-6 w-6" /></span>
      <span class="min-w-0 flex-1">
        <span class="block font-semibold">Verify to start transacting</span>
        <span class="block text-sm text-app-muted">Photograph your ID (front and back) and take three selfies. We read your details from the ID: nothing to type.</span>
      </span>
      <x-heroicon-o-chevron-right class="h-5 w-5 shrink-0 text-app-accent" />
    </a>
  @endunless

  {{-- Wallet and this month side by side (half each), then the money chart and transactions across the full width --}}
  <div class="grid gap-6 lg:grid-cols-2">
    @include('dashboard.partials.wallet')
    @include('dashboard.partials.summary')
  </div>

  @include('pay.partials.chart')

  <section class="order-2 rounded-2xl border border-app-line bg-app-panel p-5">
      <div class="mb-2 flex items-baseline justify-between">
        <h2 class="text-base font-semibold">Latest transactions</h2>
        <a href="{{ route('pay.index') }}" class="text-[13px] text-app-soft hover:text-app-accent">See all</a>
      </div>
      <p x-show="loading" class="py-8 text-center text-sm text-app-muted">Loading…</p>
      <div x-show="!loading && !rows.length" x-cloak class="flex flex-col items-center gap-3 py-10 text-center">
        <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-app-tile text-app-accent"><x-heroicon-o-arrows-right-left class="h-6 w-6" /></span>
        <p class="font-semibold">No transactions yet</p>
        <p class="max-w-xs text-sm text-app-muted">Receive into your wallet, or send to any M-PESA number, paybill, till, bank or Sharet @name.</p>
      </div>
      <ul x-show="rows.length" x-cloak class="space-y-1.5">
        <template x-for="r in rows" :key="r.id">@include('pay.partials.row')</template>
      </ul>
  </section>

  @include('dashboard.partials.money')
</div>
@endsection
