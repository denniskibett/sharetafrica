{{-- Customer home (personal, merchant, business, developer). Design: brand canvas "Mobile home" and "Web dashboard":
     lime balance card, round quick actions, wallets, money in/out and activity. Data: dashboard.data, dashboard.activity. --}}
@extends('layouts.app')
@section('title', 'Home')

@php
    $actions = [
        'individual' => [['Send', 'wallet.transfer', 'paper-airplane'], ['Receive', 'wallet.deposit', 'arrow-down-tray'], ['QR code', 'individual.qr', 'qr-code'], ['Wallet', 'wallet.index', 'wallet']],
        'merchant' => [['Accept', 'merchant.accept', 'banknotes'], ['Settle', 'merchant.settle', 'building-library'], ['QR codes', 'merchant.qr', 'qr-code'], ['Reconcile', 'merchant.reconcile', 'scale']],
        'business' => [['Pay', 'business.pay', 'paper-airplane'], ['Invoices', 'business.invoices.index', 'document-text'], ['Trade', 'business.trade-finance', 'globe-europe-africa'], ['Treasury', 'business.treasury', 'building-library']],
        'techie' => [['API keys', 'api-keys.index', 'key'], ['Webhooks', 'developer.webhooks.index', 'bolt'], ['Sandbox', 'developer.sandbox', 'beaker'], ['Logs', 'developer.logs', 'command-line']],
    ][$lane] ?? [];
    $level = $user->kyc_level ?? 'none';
@endphp

@section('content')
<div x-data="{ s: {}, rows: [], loading: true }"
     x-init="s = (await (await fetch('{{ route('dashboard.data') }}', { headers: { Accept: 'application/json' } })).json()).stats;
             rows = (await (await fetch('{{ route('dashboard.activity') }}?per_page=6', { headers: { Accept: 'application/json' } })).json()).data; loading = false"
     class="mx-auto flex max-w-[1240px] flex-col gap-6">

  <h1 class="hidden md:block">Overview</h1>

  {{-- Balance (lime) + wallets --}}
  <div class="grid gap-4 md:grid-cols-[minmax(0,1.3fr)_repeat(3,minmax(0,1fr))]">
    <div class="flex flex-col gap-2.5 rounded-[20px] bg-[#D4FF3D] p-5 text-[#050507]">
      <span class="app-mono text-xs tracking-[0.16em]">TOTAL BALANCE · KES</span>
      <span class="app-mono text-[32px] leading-tight" x-text="loading ? '—' : Number(s.balance ?? 0).toLocaleString('en-KE', { minimumFractionDigits: 2 })">—</span>
      <div class="flex flex-wrap gap-2">
        <span class="app-mono rounded-full bg-black/10 px-2.5 py-1 text-xs" x-text="'In ' + sharet.number(s.in) + ' this month'"></span>
        <span class="app-mono rounded-full bg-black/10 px-2.5 py-1 text-xs" x-text="'Out ' + sharet.number(s.out)"></span>
      </div>
    </div>
    @foreach ([['KES', true], ['UGX', false], ['TZS', false]] as [$cur, $live])
      <div class="hidden flex-col gap-2 rounded-2xl border border-white/[0.08] bg-[#111114] p-5 md:flex">
        <span class="app-mono text-xs text-[#80807A]">{{ $cur }}</span>
        @if ($live)
          <span class="app-mono text-[22px]" x-text="sharet.number(s.balance)">—</span>
        @else
          <span class="app-mono text-[22px] text-[#5A5A55]">Soon</span>
        @endif
      </div>
    @endforeach
  </div>

  {{-- Quick actions --}}
  <div class="grid grid-cols-4 gap-2.5 md:max-w-xl">
    @foreach ($actions as [$label, $route, $icon])
      @php $ready = Route::has($route); @endphp
      <a @if ($ready) href="{{ route($route) }}" @else aria-disabled="true" title="{{ $label }}: coming soon" @endif class="group flex flex-col items-center gap-2 text-[13px] {{ $ready ? '' : 'opacity-50' }}">
        <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#18181C] transition group-hover:bg-[#232328] {{ $loop->first && $ready ? 'text-[#D4FF3D]' : '' }}">
          <x-dynamic-component :component="'heroicon-o-'.$icon" class="h-6 w-6" />
        </span>
        <span>{{ $label }}</span>
        @unless ($ready)<span class="app-mono -mt-1.5 text-[10px] uppercase tracking-wider text-[#5A5A55]">Soon</span>@endunless
      </a>
    @endforeach
  </div>

  {{-- Verification nudge (lime outline on black) --}}
  @if ($level !== 'standard')
    <a href="{{ route('profile.edit') }}#{{ $level === 'none' ? 'personal' : 'verification' }}" class="flex items-center gap-4 rounded-2xl border border-[#D4FF3D]/40 bg-[#111114] p-4 hover:border-[#D4FF3D]">
      <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#D4FF3D] text-[#050507]"><x-heroicon-o-shield-check class="h-6 w-6" /></span>
      <span class="min-w-0 flex-1">
        <span class="block font-semibold">{{ $level === 'none' ? 'Add your M-Pesa number' : 'Get bank-grade verified' }}</span>
        <span class="block text-sm text-[#80807A]">{{ $level === 'none' ? 'It opens your basic account: send and receive across East Africa.' : 'Unlock loans, cards, trade and higher limits. About 5 minutes.' }}</span>
      </span>
      <x-heroicon-o-chevron-right class="h-5 w-5 shrink-0 text-[#D4FF3D]" />
    </a>
  @endif

  <div class="grid gap-6 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,1fr)]">
    {{-- Activity --}}
    <section class="rounded-2xl border border-white/[0.08] bg-[#111114] p-5">
      <div class="mb-2 flex items-baseline justify-between">
        <h2 class="text-base font-semibold">Activity</h2>
        <a href="{{ route('wallet.index') }}" class="text-[13px] text-[#C9C9C2] hover:text-[#D4FF3D]">See all</a>
      </div>
      <p x-show="loading" class="py-8 text-center text-sm text-[#80807A]">Loading…</p>
      <div x-show="!loading && !rows.length" x-cloak class="flex flex-col items-center gap-3 py-10 text-center">
        <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[#18181C] text-[#D4FF3D]"><x-heroicon-o-arrows-right-left class="h-6 w-6" /></span>
        <p class="font-semibold">No activity yet</p>
        <p class="max-w-xs text-sm text-[#80807A]">Money you send and receive shows here, with where it went and when.</p>
      </div>
      <ul x-show="rows.length" x-cloak>
        <template x-for="r in rows" :key="r.id">
          <li class="flex items-center gap-3 border-b border-white/[0.08] py-3 last:border-0">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#18181C]" :class="r.type.tone === 'success' ? 'text-[#D4FF3D]' : 'text-[#C9C9C2]'">
              <x-heroicon-o-arrow-down-left x-show="r.type.tone === 'success'" class="h-5 w-5" /><x-heroicon-o-arrow-up-right x-show="r.type.tone !== 'success'" class="h-5 w-5" />
            </span>
            <span class="min-w-0 flex-1"><span class="block text-sm" x-text="r.type.label"></span><span class="block text-xs text-[#80807A]" x-text="r.date + ' · ' + r.status.label"></span></span>
            <span class="app-mono text-sm" :class="r.type.tone === 'success' && 'text-[#D4FF3D]'" x-text="(r.type.tone === 'success' ? '+' : '−') + sharet.number(r.amount)"></span>
          </li>
        </template>
      </ul>
    </section>

    {{-- Money in / out --}}
    <x-ui.chart :url="route('dashboard.chart', 'money')" title="Money in & out" subtitle="Last 6 months" :height="240" class="!border-white/[0.08] !bg-[#111114]" />
  </div>
</div>
@endsection
