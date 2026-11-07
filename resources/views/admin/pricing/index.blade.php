{{-- Staff › Pricing: the business tariff (docs/v2/INTEGRATION.md §7). Table 1 = fee by movement and amount band;
     Table 2 = discount by monthly outside volume. Saving adds a new version with an effective date. Icons only. --}}
@extends('layouts.app')
@section('title', 'Pricing')

@php
    $kes = fn ($c) => $c === null ? '' : rtrim(rtrim(number_format($c / 100, 2, '.', ''), '0'), '.');
    $show = fn ($c) => $c === null ? 'and above' : 'KES '.number_format($c / 100);
    $input = 'h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 focus:border-violet-400 focus:outline-none focus:ring-4 focus:ring-violet-500/10 dark:border-gray-700 dark:text-white/90';
    $bands = $current?->bands ?? [];
    $tiers = $current?->volume_tiers ?? [['from' => 0, 'discount_bp' => 0]];
@endphp

@section('content')
<div class="mx-auto max-w-[1100px] space-y-6">
  <div>
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Pricing</h1>
    <p class="mt-1 max-w-3xl text-sm text-gray-500 dark:text-gray-400">
      Fees for businesses using the API. Table 1 sets the fee for each kind of movement by amount; Table 2 discounts it by the business's
      outside payouts this month. Sharet ↔ Sharet is always free. The personal app keeps its own tariff.
    </p>
  </div>

  @if (session('status'))<div class="rounded-xl bg-emerald-50 p-4 text-sm text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-200" role="status">{{ session('status') }}</div>@endif
  @if ($errors->any())<div class="rounded-xl bg-red-50 p-4 text-sm text-red-800 dark:bg-red-500/10 dark:text-red-200" role="alert">{{ $errors->first() }}</div>@endif

  @if ($current)
    <p class="text-sm text-gray-600 dark:text-gray-300">In force: <b>version {{ $current->version }}</b> since {{ $current->effective_from->timezone('Africa/Nairobi')->format('j M Y, H:i') }} · excise {{ $current->excise_bp / 100 }}% on the fee{{ $current->notes ? ' · '.$current->notes : '' }}</p>
  @endif

  <form method="POST" action="{{ route('admin.pricing.store') }}" class="space-y-6">
    @csrf
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
      <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Table 1 · By transaction</h2>
      <p class="text-sm text-gray-500">Each row covers amounts up to its limit. Fee = fixed + percent of the amount, never more than the cap. Leave a row blank to drop it; the last row has no limit.</p>
      @foreach (\App\Models\Tariff::MOVEMENTS as $movement => $label)
        @continue($movement === 'sharet')
        @php $rows = array_merge($bands[$movement] ?? [], [['up_to' => null, 'fixed' => null, 'percent_bp' => null, 'cap' => null]]); @endphp
        <details class="mt-4 rounded-xl border border-gray-100 dark:border-gray-800" @if ($loop->index < 4) open @endif>
          <summary class="cursor-pointer px-4 py-3 text-sm font-semibold text-gray-800 dark:text-white/90">{{ $label }}</summary>
          <div class="overflow-x-auto px-4 pb-4">
            <table class="w-full min-w-[560px] text-sm">
              <thead><tr class="text-left text-xs text-gray-500"><th class="py-2 pr-2">Up to (KES, blank = and above)</th><th class="pr-2">Fixed (KES)</th><th class="pr-2">Percent</th><th>Cap (KES)</th></tr></thead>
              <tbody>
                @foreach ($rows as $i => $b)
                  <tr>
                    <td class="py-1 pr-2"><input class="{{ $input }}" name="bands[{{ $movement }}][{{ $i }}][up_to]" value="{{ $loop->last ? '' : $kes($b['up_to']) }}" placeholder="{{ $loop->last ? 'add a band' : 'and above' }}"></td>
                    <td class="pr-2"><input class="{{ $input }}" name="bands[{{ $movement }}][{{ $i }}][fixed]" value="{{ $loop->last ? '' : $kes($b['fixed']) }}"></td>
                    <td class="pr-2"><input class="{{ $input }}" name="bands[{{ $movement }}][{{ $i }}][percent]" value="{{ $loop->last || $b['percent_bp'] === null ? '' : $b['percent_bp'] / 100 }}" placeholder="e.g. 0.5"></td>
                    <td><input class="{{ $input }}" name="bands[{{ $movement }}][{{ $i }}][cap]" value="{{ $loop->last ? '' : $kes($b['cap']) }}"></td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </details>
      @endforeach
    </div>

    <div class="grid gap-6 md:grid-cols-2">
      <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Table 2 · By monthly volume</h2>
        <p class="text-sm text-gray-500">From this much outside payouts in the month, fees drop by this much. The first tier starts at 0.</p>
        <table class="mt-3 w-full text-sm">
          <thead><tr class="text-left text-xs text-gray-500"><th class="py-2 pr-2">From (KES)</th><th>Discount (%)</th></tr></thead>
          <tbody>
            @foreach (array_merge($tiers, [['from' => null, 'discount_bp' => null]]) as $i => $t)
              <tr>
                <td class="py-1 pr-2"><input class="{{ $input }}" name="tiers[{{ $i }}][from]" value="{{ $t['from'] === null ? '' : $kes($t['from']) }}" placeholder="add a tier"></td>
                <td><input class="{{ $input }}" name="tiers[{{ $i }}][discount]" value="{{ $t['discount_bp'] === null ? '' : $t['discount_bp'] / 100 }}"></td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="space-y-4 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Save as a new version</h2>
        <label class="block text-sm text-gray-600 dark:text-gray-300">In force from
          <input type="datetime-local" class="{{ $input }} mt-1" name="effective_from" value="{{ now('Africa/Nairobi')->addHour()->format('Y-m-d\TH:00') }}" required></label>
        <label class="block text-sm text-gray-600 dark:text-gray-300">Excise on the fee (%)
          <input class="{{ $input }} mt-1" name="excise_percent" value="{{ ($current?->excise_bp ?? 1500) / 100 }}" required></label>
        <label class="block text-sm text-gray-600 dark:text-gray-300">Why the change
          <input class="{{ $input }} mt-1" name="notes" maxlength="500" placeholder="e.g. Cashia rates agreed"></label>
        <button class="inline-flex h-11 items-center gap-2 rounded-full bg-gray-900 px-5 text-sm font-semibold text-white hover:bg-gray-800 dark:bg-[#D4FF3D] dark:text-gray-900"><x-heroicon-o-check class="h-4 w-4" /> Save version {{ ($versions->max('version') ?? 0) + 1 }}</button>
        <p class="text-xs text-gray-500">Quotes already given keep their price. Every payment records the version it was priced with.</p>
      </div>
    </div>
  </form>

  <div class="grid gap-6 md:grid-cols-2">
    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
      <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Sharet companies</h2>
      <p class="text-sm text-gray-500">Marked companies are priced one volume tier up.</p>
      <div class="mt-3 divide-y divide-gray-100 dark:divide-gray-800">
        @forelse ($companies as $c)
          <form method="POST" action="{{ route('admin.pricing.group', $c) }}" class="flex items-center gap-3 py-2">@csrf
            <span class="min-w-0 flex-1 text-sm"><b>{{ $c->name }}</b> <span class="text-gray-500">{{ $c->sharet_id }}</span></span>
            <button class="h-8 rounded-full px-3 text-xs font-semibold {{ $c->is_group_company ? 'bg-emerald-100 text-emerald-900' : 'bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-gray-200' }}">{{ $c->is_group_company ? 'Sharet company' : 'Mark as Sharet company' }}</button>
          </form>
        @empty
          <p class="py-3 text-sm text-gray-500">No verified businesses yet.</p>
        @endforelse
      </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
      <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Versions</h2>
      <div class="mt-3 divide-y divide-gray-100 text-sm dark:divide-gray-800">
        @foreach ($versions as $v)
          <p class="py-2"><b>v{{ $v->version }}</b> · from {{ $v->effective_from->timezone('Africa/Nairobi')->format('j M Y, H:i') }}{{ $current?->is($v) ? ' · in force' : ($v->effective_from->isFuture() ? ' · scheduled' : '') }}<br><span class="text-gray-500">{{ $v->notes }}</span></p>
        @endforeach
      </div>
    </div>
  </div>
</div>
@endsection
