{{-- Dashboard › API approvals: live API batches above the threshold wait for a second person from the same business
     (docs/v2/INTEGRATION.md §2, §4). Approve or reject asks for the password again. Icons only. --}}
@extends('layouts.app')
@section('title', 'API approvals')

@section('content')
<div class="mx-auto max-w-[1100px]">
  <div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">API approvals</h1>
    <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
      Batches sent through the API that total more than KES {{ number_format(\App\Api\Settlements::threshold() / 100) }} wait here until someone else from your business approves them.
      The person who owns the API key can't approve their own batch.
    </p>
  </div>

  @if ($wallet)
    <div class="mb-6 flex flex-col gap-4 rounded-2xl bg-gray-900 p-5 text-white sm:flex-row sm:items-center">
      <div class="min-w-0 flex-1">
        <p class="text-xs uppercase tracking-wider text-white/60">{{ $company->name }} · business wallet</p>
        <p class="mt-1 text-2xl font-semibold">KES {{ number_format($balance / 100, 2) }}</p>
        <p class="mt-1 text-sm text-white/70">{{ $wallet->sharet_id }} · {{ '@'.$wallet->handle }} · {{ $wallet->kyc_level === 'business' ? 'verified' : 'not verified yet' }}</p>
      </div>
      <p class="max-w-xs text-sm text-white/70">Your API keys collect into and pay out from this wallet. Settlements are tied to its Sharet ID.</p>
    </div>
  @endif

  @if (session('status'))<div class="mb-4 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-200" role="status">{{ session('status') }}</div>@endif
  @if ($errors->any())<div class="mb-4 rounded-xl bg-red-50 p-4 text-sm text-red-800 dark:bg-red-500/10 dark:text-red-200" role="alert">{{ $errors->first() }}</div>@endif

  <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
    @forelse ($rows as $s)
      <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 last:border-0 dark:border-gray-800 md:flex-row md:items-center">
        <div class="min-w-0 flex-1">
          <p class="font-semibold text-gray-900 dark:text-white">KES {{ number_format($s->total / 100, 2) }} · {{ $s->item_count }} {{ \Illuminate\Support\Str::plural('payment', $s->item_count) }}</p>
          <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ $s->reference ?: $s->public_id }} · sent by {{ $s->user?->name }} with “{{ $s->key?->name }}” · {{ $s->created_at->timezone('Africa/Nairobi')->format('j M, H:i') }}</p>
          @if ($s->approver)<p class="mt-0.5 text-xs text-gray-500">{{ $s->status === 'rejected' ? 'Rejected' : 'Approved' }} by {{ $s->approver->name }}, {{ $s->approved_at->timezone('Africa/Nairobi')->format('j M, H:i') }}</p>@endif
        </div>
        <span @class(['inline-flex h-7 items-center rounded-full px-3 text-xs font-semibold',
          'bg-amber-100 text-amber-900' => $s->status === 'awaiting_approval',
          'bg-emerald-100 text-emerald-900' => $s->status === 'completed',
          'bg-red-100 text-red-900' => in_array($s->status, ['rejected', 'failed', 'partially_failed'], true),
          'bg-gray-100 text-gray-800' => $s->status === 'processing'])>{{ str_replace('_', ' ', ucfirst($s->status)) }}</span>
        @if (\App\Api\Settlements::canApprove($s, $me))
          <div class="flex gap-2">
            <form method="POST" action="{{ route('pay.settlements.reject', $s) }}">@csrf<button class="h-10 rounded-full px-4 text-sm font-semibold text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-white/10">Reject</button></form>
            <form method="POST" action="{{ route('pay.settlements.approve', $s) }}">@csrf<button class="inline-flex h-10 items-center gap-2 rounded-full bg-gray-900 px-4 text-sm font-semibold text-white hover:bg-gray-800 dark:bg-[#D4FF3D] dark:text-gray-900"><x-heroicon-o-check class="h-4 w-4" /> Approve</button></form>
          </div>
        @endif
      </div>
    @empty
      <div class="flex flex-col items-center gap-2 px-5 py-14 text-center">
        <x-heroicon-o-check-badge class="h-8 w-8 text-gray-400" />
        <p class="font-semibold text-gray-900 dark:text-white">Nothing to approve</p>
        <p class="max-w-sm text-sm text-gray-500">Large API batches from your business will appear here.</p>
      </div>
    @endforelse
  </div>
</div>
@endsection
