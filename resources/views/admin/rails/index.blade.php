{{-- System › Payment rails: which partner moves each kind of payment, changed by two people. Design: canvas page "Phase 1 · Payment rails". --}}
@extends('layouts.app')
@section('title', 'Payment rails')

@php
  $card = 'rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]';
  $select = 'h-10 rounded-xl border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white';
  $btn = 'inline-flex h-10 shrink-0 items-center gap-2 rounded-full px-4 text-sm font-semibold';
  $dark = $btn.' bg-gray-900 text-white hover:bg-gray-800 dark:bg-[#D4FF3D] dark:text-gray-900';
  $soft = $btn.' bg-gray-100 text-gray-800 hover:bg-gray-200 dark:bg-white/10 dark:text-gray-100';
  $dot = ['cloud9' => 'bg-blue-600', 'daraja' => 'bg-emerald-600', 'simulator' => 'bg-gray-400'];
@endphp

@section('content')
<div class="mx-auto max-w-[1200px] space-y-6">
  <div class="flex flex-wrap items-end justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Payment rails</h1>
      <p class="mt-1 max-w-3xl text-sm text-gray-500 dark:text-gray-400">Every payment runs through Sharet: one reference, one ledger, one receipt. Here you choose which partner moves each kind of payment. A change needs a reason and a second person's approval, and only new payments take the new route. Customers never see which partner carried their money.</p>
    </div>
    <a href="{{ route('admin.rails.activity') }}" class="{{ $soft }}"><x-heroicon-o-list-bullet class="h-4 w-4" /> Activity</a>
  </div>

  @if (session('status'))
    <p class="rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-300" role="status">{{ session('status') }}</p>
  @endif
  @if ($errors->any())
    <p class="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-800 dark:bg-red-500/10 dark:text-red-300" role="alert">{{ $errors->first() }}</p>
  @endif

  <div class="grid gap-3 sm:grid-cols-3">
    @foreach ($balances as [$label, $cents])
      <div class="{{ $card }} p-4">
        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $label }}</p>
        <p class="stat-num mt-1">{{ \App\Http\Controllers\Admin\RailsController::money($cents) }}</p>
      </div>
    @endforeach
  </div>

  <div class="{{ $card }}">
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
      <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Which partner moves each kind of payment</h2>
      <p class="text-xs text-gray-500">Test list: {{ $testList ? implode(', ', $testList) : 'empty (everyone)' }}</p>
    </div>
    <div class="divide-y divide-gray-100 dark:divide-gray-800">
      @foreach ($movements as $movement => [$label, $what])
        @php($route = $routes[$movement] ?? null)
        @php($change = $pending->firstWhere('movement', $movement))
        <div class="space-y-3 px-5 py-4">
          <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
            <div class="min-w-0 flex-1">
              <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $label }}</p>
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ $what }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-x-6 gap-y-1 text-sm">
              @if ($route)
                <span class="inline-flex items-center gap-2 font-semibold text-gray-900 dark:text-white"><i class="h-2 w-2 rounded-full {{ $dot[$route->rail] ?? 'bg-gray-400' }}"></i>{{ \App\Models\RailRoute::RAILS[$route->rail] ?? $route->rail }}</span>
                <span class="text-gray-500">If refused: {{ $route->fallback ? \App\Models\RailRoute::RAILS[$route->fallback] : 'nothing else' }}</span>
                <span class="text-gray-500">{{ \App\Models\RailRoute::AUDIENCES[$route->audience] }}</span>
                <span class="text-xs text-gray-400">Approved by {{ $route->approver?->name ?? '—' }} · {{ $route->approved_at?->timezone('Africa/Nairobi')->format('j M, H:i') }}</span>
              @else
                @php($legacyRail = $movement === 'collect' ? $legacy['collect'] : $legacy['payout'])
                <span class="text-gray-500">Not switched yet: settings file ({{ \App\Models\RailRoute::RAILS[$legacyRail] ?? $legacyRail }} for the test list, else {{ \App\Models\RailRoute::RAILS[config('rails.default')] ?? config('rails.default') }})</span>
              @endif
            </div>
          </div>

          @if ($change)
            <div class="flex flex-col gap-3 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:bg-amber-500/10 dark:text-amber-200 md:flex-row md:items-center">
              <p class="flex-1"><b>Waiting for a second person:</b> {{ \App\Models\RailRoute::RAILS[$change->rail] }}{{ $change->fallback ? ', else '.\App\Models\RailRoute::RAILS[$change->fallback] : '' }} · {{ \App\Models\RailRoute::AUDIENCES[$change->audience] }} · proposed by {{ $change->proposer->name }} {{ $change->created_at->timezone('Africa/Nairobi')->format('j M, H:i') }} · “{{ $change->reason }}”</p>
              <div class="flex gap-2">
                @if ($change->proposed_by !== auth()->id())
                  <form method="POST" action="{{ route('admin.rails.approve', $change) }}">@csrf<button class="{{ $dark }}"><x-heroicon-o-lock-closed class="h-4 w-4" /> Approve</button></form>
                @endif
                <form method="POST" action="{{ route('admin.rails.reject', $change) }}">@csrf<button class="{{ $soft }}">{{ $change->proposed_by === auth()->id() ? 'Withdraw' : 'Turn down' }}</button></form>
              </div>
            </div>
          @endif

          <details class="group">
            <summary class="cursor-pointer text-sm font-medium text-gray-700 underline-offset-2 hover:underline dark:text-gray-300">Propose a change</summary>
            <form method="POST" action="{{ route('admin.rails.propose') }}" class="mt-3 grid gap-3 md:grid-cols-[repeat(3,minmax(0,1fr))_2fr_auto] md:items-end">
              @csrf
              <input type="hidden" name="movement" value="{{ $movement }}">
              <label class="text-xs text-gray-500">Primary rail
                <select name="rail" class="{{ $select }} mt-1 w-full">@foreach ($rails as $k => $name)<option value="{{ $k }}" @selected(($route->rail ?? null) === $k)>{{ $name }}</option>@endforeach</select></label>
              <label class="text-xs text-gray-500">If it refuses before accepting
                <select name="fallback" class="{{ $select }} mt-1 w-full"><option value="">Nothing else</option>@foreach ($rails as $k => $name)<option value="{{ $k }}" @selected(($route->fallback ?? null) === $k)>{{ $name }}</option>@endforeach</select></label>
              <label class="text-xs text-gray-500">Who
                <select name="audience" class="{{ $select }} mt-1 w-full">@foreach (\App\Models\RailRoute::AUDIENCES as $k => $name)<option value="{{ $k }}" @selected(($route->audience ?? 'named') === $k)>{{ $name }}</option>@endforeach</select></label>
              <label class="text-xs text-gray-500">Why
                <input name="reason" required maxlength="300" placeholder="e.g. Cloud9 sandbox tests passed" class="{{ $select }} mt-1 w-full"></label>
              <button class="{{ $dark }}">Propose</button>
            </form>
          </details>
        </div>
      @endforeach
    </div>
    <p class="border-t border-gray-100 px-5 py-3 text-xs text-gray-500 dark:border-gray-800">A fallback is only tried when the primary partner refuses before accepting (down, no float, a key problem). Money in by M-PESA prompt that Cloud9 can't take always goes to Sharet's own M-PESA paybill, unless a route here says otherwise (RAIL_COLLECT_FALLBACK). After a timeout nothing is resent: the first partner may still pay, so the payment is checked by its id instead.</p>
  </div>

  <div class="grid gap-6 lg:grid-cols-2">
    <div class="{{ $card }} space-y-4 p-5">
      <div class="flex items-center justify-between gap-2">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Cloud9</h2>
        <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $cloud9['connected'] ? 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300' : 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300' }}">{{ $cloud9['connected'] ? strtoupper($cloud9['environment']) : 'NOT CONNECTED' }}</span>
      </div>
      <dl class="space-y-2 text-sm">
        <div class="flex justify-between gap-4"><dt class="text-gray-500">API key and secrets</dt><dd><a href="{{ route('integrations.index') }}" class="font-medium underline">Integrations › Cloud9</a></dd></div>
        <div class="flex justify-between gap-4"><dt class="text-gray-500">Callback URL (PAYIN and PAYOUT)</dt><dd class="break-all text-right font-mono text-xs">{{ $callbackUrl }}</dd></div>
        <div class="flex justify-between gap-4"><dt class="text-gray-500">Callback secrets</dt><dd class="flex gap-2">@foreach ($cloud9['secrets'] as $event => $saved)<span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $saved ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-amber-50 text-amber-800 dark:bg-amber-500/15 dark:text-amber-300' }}">{{ $event }} {{ $saved ? 'saved' : 'missing' }}</span>@endforeach</dd></div>
        <div class="flex justify-between gap-4"><dt class="text-gray-500">Allow-list for the live key</dt><dd class="font-mono text-xs">this server's address</dd></div>
      </dl>
      <div class="border-t border-gray-100 pt-4 dark:border-gray-800">
        <p class="text-sm font-semibold text-gray-900 dark:text-white">Sandbox checks <span class="font-normal text-gray-500">(no real money; payments go to your own number)</span></p>
        <div class="mt-3 flex flex-wrap gap-2">
          @foreach (['wallets' => 'Read wallets', 'prompt' => 'M-PESA prompt · KES 20', 'payout' => 'Send · KES 10'] as $check => $label)
            <form method="POST" action="{{ route('admin.rails.test') }}">@csrf<input type="hidden" name="check" value="{{ $check }}"><button class="{{ $soft }}" @disabled(! $cloud9['connected'])><x-heroicon-o-play class="h-4 w-4" /> {{ $label }}</button></form>
          @endforeach
        </div>
        <form method="POST" action="{{ route('admin.rails.test') }}" class="mt-3 flex flex-wrap items-end gap-2">
          @csrf<input type="hidden" name="check" value="status">
          <select name="kind" class="{{ $select }}" aria-label="Kind"><option value="deposit">Deposit</option><option value="payout">Payout</option></select>
          <input name="id" placeholder="Cloud9 id, e.g. 68MW27JX" class="{{ $select }} w-48" aria-label="Cloud9 id">
          <button class="{{ $soft }}" @disabled(! $cloud9['connected'])>Check status</button>
        </form>
        @if ($t = session('cloud9_test'))
          <div class="mt-4 rounded-xl {{ $t['ok'] ? 'bg-emerald-50 text-emerald-900 dark:bg-emerald-500/10 dark:text-emerald-200' : 'bg-red-50 text-red-900 dark:bg-red-500/10 dark:text-red-200' }} px-4 py-3 text-sm" role="status">
            <p class="whitespace-pre-line font-medium">{{ $t['summary'] }}</p>
            @if ($t['body'])<details class="mt-2"><summary class="cursor-pointer text-xs">Response (HTTP {{ $t['status'] }})</summary><pre class="mt-2 max-h-72 overflow-auto rounded-lg bg-gray-900 p-3 text-xs text-gray-100">{{ $t['body'] }}</pre></details>@endif
          </div>
        @endif
      </div>
    </div>

    <div class="{{ $card }} p-5">
      <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">Recent decisions</h2>
      <ul class="mt-3 divide-y divide-gray-100 text-sm dark:divide-gray-800">
        @forelse ($history as $h)
          <li class="py-2.5">
            <p class="text-gray-900 dark:text-white"><b class="font-semibold">{{ \App\Models\RailRoute::MOVEMENTS[$h->movement][0] ?? $h->movement }}</b> → {{ \App\Models\RailRoute::RAILS[$h->rail] ?? $h->rail }} · {{ \App\Models\RailRoute::AUDIENCES[$h->audience] ?? $h->audience }}</p>
            <p class="text-xs text-gray-500">{{ ucfirst($h->status) }} by {{ $h->decider?->name ?? '—' }} · proposed by {{ $h->proposer?->name }} · {{ $h->decided_at?->timezone('Africa/Nairobi')->format('j M, H:i') }}{{ $h->decision_note ? ' · '.$h->decision_note : '' }}</p>
          </li>
        @empty
          <li class="py-6 text-center text-gray-500">No changes yet.</li>
        @endforelse
      </ul>
    </div>
  </div>
</div>
@endsection
