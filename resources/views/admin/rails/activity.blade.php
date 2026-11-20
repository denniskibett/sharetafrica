{{-- System › Payment rails › Activity: every request on every rail, with the Sharet reference beside the partner's id and receipt. --}}
@extends('layouts.app')
@section('title', 'Rail activity')

@php
  $card = 'rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]';
  $chip = fn ($on) => 'inline-flex h-8 items-center rounded-full border px-3 text-sm '.($on ? 'border-gray-900 bg-gray-900 text-white dark:border-[#D4FF3D] dark:bg-[#D4FF3D] dark:text-gray-900' : 'border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300');
  $pill = ['succeeded' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300', 'failed' => 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-300',
           'pending' => 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300', 'unknown' => 'bg-amber-50 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300'];
@endphp

@section('content')
<div class="mx-auto max-w-[1200px] space-y-6">
  <div class="flex flex-wrap items-end justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Rail activity</h1>
      <p class="mt-1 max-w-3xl text-sm text-gray-500 dark:text-gray-400">Every request Sharet sent to a payment partner, newest first: three references (Sharet's, the payment partner's, and the network's own: the M-PESA receipt, bank or card reference), what the partner charged, and whether its webhook arrived.</p>
    </div>
    <a href="{{ route('admin.rails.index') }}" class="inline-flex h-10 items-center gap-2 rounded-full bg-gray-100 px-4 text-sm font-semibold text-gray-800 hover:bg-gray-200 dark:bg-white/10 dark:text-gray-100"><x-heroicon-o-arrow-left class="h-4 w-4" /> Payment rails</a>
  </div>

  <div class="grid gap-3 sm:grid-cols-3">
    @forelse ($stats as $name => $s)
      <div class="{{ $card }} p-4">
        <p class="text-xs text-gray-500">{{ \App\Models\RailRoute::RAILS[$name] ?? $name }} · today</p>
        <p class="stat-num mt-1">{{ $s['final'] ? round(100 * $s['done'] / $s['final'], 1).'%' : '—' }}</p>
        <p class="text-xs text-gray-500">{{ $s['done'] }} of {{ $s['final'] }} finished paid · median {{ $s['median'] !== null ? round($s['median']).' s' : '—' }} · charges {{ \App\Http\Controllers\Admin\RailsController::money($s['charges']) }}</p>
      </div>
    @empty
      <div class="{{ $card }} p-4 text-sm text-gray-500 sm:col-span-3">Nothing sent to a payment partner today.</div>
    @endforelse
  </div>

  <div class="{{ $card }}">
    <div class="flex flex-wrap gap-2 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
      <a class="{{ $chip(! $rail && ! $status) }}" href="{{ route('admin.rails.activity') }}">All</a>
      @foreach (\App\Models\RailRoute::RAILS as $k => $name)
        <a class="{{ $chip($rail === $k) }}" href="{{ route('admin.rails.activity', ['rail' => $k, 'status' => $status]) }}">{{ $name }}</a>
      @endforeach
      <span class="mx-1 w-px bg-gray-200 dark:bg-gray-700"></span>
      @foreach (['pending' => 'Waiting', 'unknown' => 'No answer yet', 'failed' => 'Failed'] as $k => $name)
        <a class="{{ $chip($status === $k) }}" href="{{ route('admin.rails.activity', ['rail' => $rail, 'status' => $k]) }}">{{ $name }}</a>
      @endforeach
    </div>
    <div class="overflow-x-auto">
      <table class="w-full min-w-[900px] text-sm">
        <thead><tr class="text-left text-xs uppercase tracking-wide text-gray-500">
          <th class="px-5 py-3 font-medium">Time</th><th class="px-3 py-3 font-medium">Sharet ref</th><th class="px-3 py-3 font-medium">Payment</th><th class="px-3 py-3 font-medium">Rail</th>
          <th class="px-3 py-3 font-medium">Rail ref</th><th class="px-3 py-3 font-medium">Network ref</th><th class="px-3 py-3 text-right font-medium">Amount</th><th class="px-3 py-3 text-right font-medium">Charge</th><th class="px-3 py-3 font-medium">Status</th><th class="px-5 py-3 font-medium">Webhook</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
          @forelse ($requests as $r)
            @php($o = $orders[$r->meta['order'] ?? ''] ?? null)
            <tr>
              <td class="whitespace-nowrap px-5 py-3 text-gray-500">{{ $r->created_at->timezone('Africa/Nairobi')->format('j M, H:i') }}</td>
              <td class="px-3 py-3 font-mono text-xs">{{ $o?->sharetReference() ?? '—' }}</td>
              <td class="px-3 py-3">{{ ['collect' => 'Money in', 'payout' => 'Money out', 'refund' => 'Refund'][$r->meta['role'] ?? $r->kind] ?? $r->kind }} · {{ $r->party }}</td>
              <td class="px-3 py-3">{{ \App\Models\RailRoute::RAILS[$r->rail] ?? $r->rail }}@if ($r->meta['fell_back_from'] ?? null)<span class="block text-xs text-amber-700">after {{ \App\Models\RailRoute::RAILS[$r->meta['fell_back_from']] ?? $r->meta['fell_back_from'] }} refused</span>@endif</td>
              <td class="px-3 py-3 font-mono text-xs">{{ $r->provider_reference ?? '—' }}</td>
              <td class="px-3 py-3 text-xs"><span class="font-mono">{{ $r->receipt && $r->receipt !== $r->provider_reference ? $r->receipt : '—' }}</span>@if ($r->meta['network'] ?? null)<span class="block text-gray-500">{{ $r->meta['network'] }}</span>@endif</td>
              <td class="px-3 py-3 text-right tabular-nums">{{ \App\Ledger\Money::format($r->amount) }}</td>
              <td class="px-3 py-3 text-right tabular-nums">{{ isset($r->meta['charge']) ? \App\Ledger\Money::format($r->meta['charge']) : '—' }}</td>
              <td class="px-3 py-3"><span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $pill[$r->status] ?? '' }}" title="{{ $r->failure }}">{{ strtoupper($r->status) }}</span></td>
              <td class="px-5 py-3 text-xs text-gray-500">{{ count($r->meta['callbacks'] ?? []) ? count($r->meta['callbacks']).' received' : ($r->checks ? 'checked by id ×'.$r->checks : 'none yet') }}</td>
            </tr>
          @empty
            <tr><td colspan="10" class="px-5 py-10 text-center text-gray-500">No requests match.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  {{ $requests->links() }}
</div>
@endsection
