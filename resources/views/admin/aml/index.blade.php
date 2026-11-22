{{-- Compliance › AML checks: payments held before any money moved, blocked attempts, and the watch list. --}}
@extends('layouts.app')
@section('title', 'AML checks')

@php
  $card = 'rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]';
  $input = 'h-10 rounded-xl border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white';
  $btn = 'inline-flex h-10 shrink-0 items-center gap-2 rounded-full px-4 text-sm font-semibold';
  $dark = $btn.' bg-gray-900 text-white hover:bg-gray-800 dark:bg-[#D4FF3D] dark:text-gray-900';
  $soft = $btn.' bg-gray-100 text-gray-800 hover:bg-gray-200 dark:bg-white/10 dark:text-gray-100';
  $kes = fn ($c) => 'KES '.\App\Ledger\Money::format((int) $c);
@endphp

@section('content')
<div class="mx-auto max-w-[1200px] space-y-6">
  <div>
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">AML checks</h1>
    <p class="mt-1 max-w-3xl text-sm text-gray-500 dark:text-gray-400">Every payment is screened before any money moves: no M-PESA prompt, payout or transfer goes out until it passes. Payments that hit a rule wait here. Approve and it carries on as if just confirmed; stop it and nothing leaves the wallet. You can't decide on your own payments.</p>
  </div>
  @if (session('status'))<p class="rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-300" role="status">{{ session('status') }}</p>@endif
  @if ($errors->any())<p class="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-800 dark:bg-red-500/10 dark:text-red-300" role="alert">{{ $errors->first() }}</p>@endif

  <div class="{{ $card }}">
    <h2 class="border-b border-gray-100 px-5 py-4 text-lg font-semibold text-gray-800 dark:border-gray-800 dark:text-white/90">Waiting for a decision ({{ $pending->count() }})</h2>
    <div class="divide-y divide-gray-100 dark:divide-gray-800">
      @forelse ($pending as $c)
        <div class="space-y-3 px-5 py-4">
          <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
              <p class="font-semibold text-gray-900 dark:text-white">{{ $c->user->name }} · {{ $kes($c->amount) }} → {{ $c->order?->recipient() ?? '—' }}</p>
              <p class="text-xs text-gray-500">{{ $c->order?->sharetReference() }} · {{ $c->user->email }} · {{ $c->user->phone }} · {{ ucfirst($c->user->kyc_level ?? 'none') }} · joined {{ $c->user->created_at?->diffForHumans() }} · held {{ $c->created_at->timezone('Africa/Nairobi')->format('j M, H:i') }}</p>
            </div>
            <a href="{{ route('users.show', $c->user_id) }}" class="text-sm font-medium underline">Open person</a>
          </div>
          <ul class="flex flex-wrap gap-2">@foreach ($c->hits as $h)<li class="rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-900 dark:bg-amber-500/10 dark:text-amber-200">{{ $h['label'] }}</li>@endforeach</ul>
          <div class="flex flex-wrap items-end gap-2">
            <form method="POST" action="{{ route('admin.aml.approve', $c) }}" class="flex items-end gap-2">@csrf<input name="note" placeholder="Why it's fine (optional)" class="{{ $input }} w-64" aria-label="Note"><button class="{{ $dark }}">Approve</button></form>
            <form method="POST" action="{{ route('admin.aml.reject', $c) }}" class="flex items-end gap-2">@csrf<input name="note" required placeholder="Why it's stopped" class="{{ $input }} w-64" aria-label="Reason"><button class="{{ $soft }}">Stop it</button></form>
          </div>
        </div>
      @empty
        <p class="px-5 py-10 text-center text-sm text-gray-500">Nothing is waiting. Every payment so far passed screening.</p>
      @endforelse
    </div>
  </div>

  <div class="grid gap-6 lg:grid-cols-2">
    <div class="{{ $card }} p-5">
      <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Recent decisions and blocks</h2>
      <ul class="mt-3 divide-y divide-gray-100 text-sm dark:divide-gray-800">
        @forelse ($recent as $c)
          <li class="py-2.5">
            <p class="text-gray-900 dark:text-white">{{ $c->user?->name }} · {{ $kes($c->amount) }} · <b>{{ ['approved' => 'Approved', 'rejected' => 'Stopped', 'closed' => 'Blocked'][$c->status] ?? $c->status }}</b></p>
            <p class="text-xs text-gray-500">{{ collect($c->hits)->pluck('label')->implode(' · ') }} · {{ $c->reviewer?->name ?? 'automatic' }} · {{ $c->updated_at->timezone('Africa/Nairobi')->format('j M, H:i') }}{{ $c->note ? ' · '.$c->note : '' }}</p>
          </li>
        @empty
          <li class="py-6 text-center text-gray-500">None yet.</li>
        @endforelse
      </ul>
    </div>
    <div class="{{ $card }} space-y-4 p-5">
      <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Watch list</h2>
      <p class="text-sm text-gray-500">Payments to or from anything here are blocked before they move.</p>
      <form method="POST" action="{{ route('admin.aml.watch') }}" class="grid gap-2 sm:grid-cols-[auto_1fr_1fr_auto]">
        @csrf
        <select name="type" class="{{ $input }}" aria-label="Type">@foreach (\App\Models\AmlWatch::TYPES as $k => $t)<option value="{{ $k }}">{{ $t }}</option>@endforeach</select>
        <input name="value" required placeholder="0712 345 678" class="{{ $input }}" aria-label="Value">
        <input name="reason" required placeholder="Why" class="{{ $input }}" aria-label="Reason">
        <button class="{{ $dark }}">Add</button>
      </form>
      <ul class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
        @forelse ($watch as $w)
          <li class="flex items-center justify-between gap-3 py-2"><span><b>{{ \App\Models\AmlWatch::TYPES[$w->type] ?? $w->type }}</b> {{ $w->value }} · <span class="text-gray-500">{{ $w->reason }}</span></span>
            <form method="POST" action="{{ route('admin.aml.unwatch', $w) }}">@csrf @method('DELETE')<button class="text-sm underline">Remove</button></form></li>
        @empty
          <li class="py-4 text-center text-gray-500">Empty.</li>
        @endforelse
      </ul>
      <details class="text-xs text-gray-500"><summary class="cursor-pointer">The rules and their figures</summary>
        <ul class="mt-2 space-y-1">
          <li>More than {{ $rules['max_payments_per_hour'] }} payments in an hour, or more than {{ $kes($rules['max_out_per_hour']) }} out in an hour: hold</li>
          <li>More than {{ $rules['structuring_count'] }} payments in a day at {{ (int) ($rules['structuring_share'] * 100) }}% or more of the per-payment limit: hold</li>
          <li>First payment to someone of {{ $kes($rules['new_payee_large']) }} or more: hold</li>
          <li>Account under {{ $rules['new_account_hours'] }} hours old paying {{ $kes($rules['new_account_large']) }} or more: hold</li>
          <li>{{ $kes($rules['in_out_min_in']) }}+ in during the hour and {{ (int) ($rules['in_out_share'] * 100) }}% of it out to {{ $rules['in_out_payees'] }}+ people: hold</li>
          <li>{{ $rules['fan_in_senders'] }}+ different people paid the wallet in a day, then money out: hold</li>
          <li>A politically exposed person paying {{ $kes($rules['pep_large']) }} or more: hold</li>
          <li>Anything on the watch list: block</li>
        </ul></details>
    </div>
  </div>
</div>
@endsection
