{{-- Revenue share: what each payment moved, what the partner kept and what Sharet kept, by rail and by kind. --}}
@extends('layouts.app')
@section('title', 'Revenue share')

@php
  $card = 'rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]';
  $kes = fn ($c) => \App\Ledger\Money::format((int) $c);
  $rail = ['cloud9' => 'Cloud9', 'daraja' => 'M-PESA paybill', 'internal' => 'Inside Sharet', 'simulator' => 'Simulator (test)'];
  $chip = fn ($on) => 'inline-flex h-9 items-center rounded-full px-4 text-sm font-medium '.($on ? 'bg-gray-900 text-white dark:bg-[#D4FF3D] dark:text-gray-900' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-gray-300');
@endphp

@section('content')
<div class="mx-auto max-w-[1200px] space-y-6">
  <div class="flex flex-wrap items-end justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Revenue share</h1>
      <p class="mt-1 max-w-3xl text-sm text-gray-500 dark:text-gray-400">Every payment is priced as the partner's own charge (Cloud9's, or Safaricom's on our paybill) plus Sharet's markup of {{ $markup['bp'] / 100 }}%, at least KES {{ $kes($markup['min']) }}, nothing up to KES {{ $kes($markup['free_up_to']) }}. Businesses get their volume discount; Sharet to Sharet is free. Here is who kept what.</p>
    </div>
    <nav class="flex flex-wrap gap-2" aria-label="Period">@foreach ($periods as $k => $t)<a class="{{ $chip($period === $k) }}" href="{{ route('admin.revenue.index', ['period' => $k]) }}">{{ $t }}</a>@endforeach</nav>
  </div>

  <div class="grid gap-3 sm:grid-cols-4">
    @foreach ([['Money moved', $total['moved'], $total['count'].' payments'], ['Partners kept', $total['partner'], 'Cloud9 and Safaricom'], ['Sharet kept', $total['sharet'], 'markup and fees, after excise'], ['Excise set aside', $total['excise'], 'owed to KRA']] as [$l, $v, $s])
      <div class="{{ $card }} p-4"><p class="text-xs text-gray-500">{{ $l }}</p><p class="stat-num mt-1">KES {{ $kes($v) }}</p><p class="text-xs text-gray-500">{{ $s }}</p></div>
    @endforeach
  </div>

  <div class="grid gap-6 lg:grid-cols-2">
    @foreach ([['By rail', $byRail, $rail], ['By kind of payment', $byKind, []]] as [$title, $groups, $names])
      <div class="{{ $card }} overflow-x-auto p-5">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $title }}</h2>
        <table class="mt-3 w-full text-sm">
          <thead><tr class="text-left text-xs uppercase tracking-wide text-gray-500"><th class="py-2 font-medium"></th><th class="py-2 text-right font-medium">Payments</th><th class="py-2 text-right font-medium">Moved</th><th class="py-2 text-right font-medium">Partner</th><th class="py-2 text-right font-medium">Sharet</th></tr></thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse ($groups as $k => $g)
              <tr><td class="py-2">{{ $names[$k] ?? $k }}</td><td class="py-2 text-right tabular-nums">{{ $g['count'] }}</td><td class="py-2 text-right tabular-nums">{{ $kes($g['moved']) }}</td><td class="py-2 text-right tabular-nums">{{ $kes($g['partner']) }}</td><td class="py-2 text-right font-semibold tabular-nums">{{ $kes($g['sharet']) }}</td></tr>
            @empty
              <tr><td colspan="5" class="py-6 text-center text-gray-500">No finished payments in this period.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    @endforeach
  </div>

  <div class="{{ $card }}">
    <h2 class="border-b border-gray-100 px-5 py-4 text-lg font-semibold text-gray-800 dark:border-gray-800 dark:text-white/90">Payment by payment</h2>
    <div class="overflow-x-auto">
      <table class="w-full min-w-[900px] text-sm">
        <thead><tr class="text-left text-xs uppercase tracking-wide text-gray-500"><th class="px-5 py-3 font-medium">When</th><th class="px-3 py-3 font-medium">Sharet ref</th><th class="px-3 py-3 font-medium">Who</th><th class="px-3 py-3 font-medium">Kind</th><th class="px-3 py-3 font-medium">Rail</th><th class="px-3 py-3 text-right font-medium">Moved</th><th class="px-3 py-3 text-right font-medium">Partner kept</th><th class="px-3 py-3 text-right font-medium">Sharet kept</th><th class="px-5 py-3 text-right font-medium">Excise</th></tr></thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
          @forelse ($rows as $r)
            <tr>
              <td class="whitespace-nowrap px-5 py-2.5 text-gray-500">{{ $r['when'] }}</td><td class="px-3 py-2.5 font-mono text-xs">{{ $r['ref'] }}</td><td class="px-3 py-2.5">{{ $r['who'] }}</td>
              <td class="px-3 py-2.5">{{ $r['kind'] }}</td><td class="px-3 py-2.5">{{ $rail[$r['rail']] ?? $r['rail'] }}</td>
              <td class="px-3 py-2.5 text-right tabular-nums">{{ $kes($r['moved']) }}</td>
              <td class="px-3 py-2.5 text-right tabular-nums">{{ $kes($r['partner']) }}@if ($r['estimated'])<span class="ml-1 text-xs text-gray-400">est.</span>@endif</td>
              <td class="px-3 py-2.5 text-right font-semibold tabular-nums">{{ $kes($r['sharet']) }}</td><td class="px-5 py-2.5 text-right tabular-nums text-gray-500">{{ $kes($r['excise']) }}</td>
            </tr>
          @empty
            <tr><td colspan="9" class="px-5 py-10 text-center text-gray-500">No finished payments in this period.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="{{ $card }} p-5">
    <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">In the ledger, all time</h2>
    <dl class="mt-3 grid gap-3 sm:grid-cols-5 text-sm">@foreach ($ledger as [$l, $v])<div><dt class="text-xs text-gray-500">{{ $l }}</dt><dd class="stat-num mt-1">KES {{ $kes($v) }}</dd></div>@endforeach</dl>
  </div>
</div>
@endsection
