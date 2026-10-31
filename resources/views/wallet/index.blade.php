{{-- Wallet overview (WalletController@index). Amounts in the wallet are stored in cents. --}}
@extends('layouts.app')
@section('title', 'Wallet')

@section('content')
<div class="mb-6">
  <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Wallet</h1>
  <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Your Sharet balance and recent movements.</p>
</div>

<div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
  <div class="relative overflow-hidden rounded-2xl bg-gray-900 p-6 text-white dark:bg-white/[0.06]">
    <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-[#D4FF3D]/20"></div>
    <p class="text-sm text-white/70">Balance</p>
    <p class="mt-2 text-3xl font-semibold tracking-tight">KES {{ number_format((float) $balance, 2) }}</p>
    <p class="mt-3 text-xs text-white/60">Sending and receiving open as each rail goes live.</p>
  </div>
</div>

<div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
  <h2 class="border-b border-gray-100 px-5 py-4 font-semibold text-gray-900 dark:border-gray-800 dark:text-white">Recent movements</h2>
  @forelse ($transactions as $t)
    <div class="flex items-center justify-between gap-4 border-b border-gray-100 px-5 py-3 text-sm last:border-0 dark:border-gray-800">
      <span class="flex items-center gap-3">
        <span @class(['flex h-9 w-9 items-center justify-center rounded-lg',
          'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300' => $t->type === 'deposit',
          'bg-rose-100 text-rose-600 dark:bg-rose-500/15 dark:text-rose-300' => $t->type !== 'deposit'])>
          <x-dynamic-component :component="$t->type === 'deposit' ? 'heroicon-o-arrow-down-left' : 'heroicon-o-arrow-up-right'" class="h-5 w-5" />
        </span>
        <span>
          <span class="block font-medium capitalize text-gray-800 dark:text-gray-200">{{ $t->type === 'deposit' ? 'Money in' : 'Money out' }}</span>
          <span class="text-xs text-gray-500 dark:text-gray-400">{{ $t->created_at?->timezone('Africa/Nairobi')->format('j M Y, H:i') }}</span>
        </span>
      </span>
      <span @class(['font-semibold', 'text-emerald-600 dark:text-emerald-400' => $t->type === 'deposit', 'text-gray-800 dark:text-gray-200' => $t->type !== 'deposit'])>
        {{ $t->type === 'deposit' ? '+' : '−' }} KES {{ number_format(abs($t->amount) / 100, 2) }}
      </span>
    </div>
  @empty
    <p class="px-5 py-12 text-center text-sm text-gray-500 dark:text-gray-400">No money has moved yet.</p>
  @endforelse
</div>
@endsection
