{{-- Help (canvas A17, web version): search the common questions, see whether the service is running, report fraud,
     contact us. Answers come from the live settings (limits from config/pricing.php), so they never drift.
     Chat, cases and the one-tap freeze in the design need a backend that does not exist yet: they are not shown. --}}
@extends('layouts.app')
@section('title', 'Help')

@php
    $kes = fn (int $cents) => 'KES '.number_format($cents / 100);
    $limits = config('pricing.limits');
    $paused = \App\Security\Lockdown::ledgerFrozen();
    $testMode = config('rails.default') === 'simulator';
    $faq = [
        ['How do I add money?', 'Tap Add on Home and enter the amount. M-PESA sends a prompt to your phone: approve it with your M-PESA PIN and the money lands in your wallet. Adding money is free.'],
        ['How much does sending cost?', 'Sending to another Sharet member (@name) is free and instant. Sending to an M-PESA number, paybill, till or bank is free up to KES 100; above that you pay Safaricom\'s charge plus a small Sharet fee. You always see the full fee before you confirm.'],
        ['What are my limits?', 'Verified accounts can send up to '.$kes($limits['basic']['per_order']).' per payment and '.$kes($limits['basic']['per_day']).' per day. Bank grade raises this to '.$kes($limits['standard']['per_order']).' per payment and '.$kes($limits['standard']['per_day']).' per day.'],
        ['Why do I need to verify my ID?', 'The law asks us to know who moves money. Photograph your ID (front and back) and take three selfies: it takes about two minutes and we read your details from the ID. Until then you can receive, but not send.'],
        ['What is my Sharet PIN?', 'Four digits you choose to approve payments from your wallet. It is not your M-PESA PIN. Never share it: Sharet staff will never ask for it.'],
        ['What is my wallet number?', 'Your M-PESA number is your Sharet wallet number, and you also have an @name. Both are on Home: tap either to copy it.'],
        ['My payment has not arrived', 'Open Transactions and tap the payment: the receipt shows where it is. If it still looks wrong, contact us with the reference on the receipt.'],
        ['When can I send to Tanzania or Uganda?', 'Tanzania is next, after the Kenya pilot. Uganda comes after Tanzania. Sending is in Kenya for now.'],
    ];
@endphp

@section('content')
<div class="mx-auto flex max-w-3xl flex-col gap-6"
     x-data="{ q: '', open: null, faq: @js(array_map(fn ($f) => strtolower($f[0].' '.$f[1]), $faq)),
               match(i) { const w = this.q.trim().toLowerCase(); return !w || w.split(/\s+/).every(t => this.faq[i].includes(t)) },
               get count() { return this.faq.filter((_, i) => this.match(i)).length } }">
  <div>
    <h1>Help</h1>
    <p class="mt-1 text-sm text-app-muted">Answers first, then a person.</p>
  </div>

  <label class="relative block">
    <span class="sr-only">Search help</span>
    <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-app-muted" />
    <input type="search" x-model="q" placeholder="Search help" autocomplete="off"
           class="h-12 w-full rounded-full border-0 bg-app-panel pl-12 pr-4 text-base text-app-ink shadow-[0_1px_2px_rgb(10_10_12/0.04),0_10px_30px_-18px_rgb(10_10_12/0.18)] placeholder:text-app-faint focus:outline-none focus:ring-2 focus:ring-app-ink/70">
  </label>

  {{-- Service status --}}
  <div @class(['flex items-start gap-3 rounded-2xl px-4 py-3 text-sm', 'bg-app-wait/10' => $paused || $testMode, 'bg-app-in/10' => ! $paused && ! $testMode])>
    <span @class(['mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full', 'bg-app-wait' => $paused || $testMode, 'bg-app-in' => ! $paused && ! $testMode])></span>
    <span>
      @if ($paused)
        <span class="font-semibold">Payments are paused.</span> <span class="text-app-soft">Your balance is safe and you can still see it. Sending and adding money will be back shortly.</span>
      @else
        <span class="font-semibold">All services are running.</span>
        @if ($testMode)<span class="text-app-soft">Test mode: payments are simulated and no real money moves.</span>@endif
      @endif
    </span>
  </div>

  <section class="rounded-[24px] bg-app-panel px-5 py-2">
    <h2 class="pt-3 text-base font-semibold">Common questions</h2>
    <ul>
      @foreach ($faq as $i => [$question, $answer])
        <li x-show="match({{ $i }})" class="border-b border-app-line last:border-0">
          <button type="button" @click="open = open === {{ $i }} ? null : {{ $i }}" :aria-expanded="open === {{ $i }}" aria-controls="faq-{{ $i }}"
                  class="flex min-h-[52px] w-full items-center gap-3 py-3 text-left text-sm font-semibold">
            <span class="flex-1">{{ $question }}</span>
            <x-heroicon-o-chevron-down class="h-5 w-5 shrink-0 text-app-muted transition" x-bind:class="open === {{ $i }} && 'rotate-180'" />
          </button>
          <div id="faq-{{ $i }}" x-show="open === {{ $i }}" x-collapse x-cloak>
            <p class="pb-4 pr-8 text-sm leading-relaxed text-app-soft">{{ $answer }}</p>
          </div>
        </li>
      @endforeach
    </ul>
    <p x-show="!count" x-cloak class="py-8 text-center text-sm text-app-muted">No answers match "<span x-text="q"></span>". Contact us below and a person will help.</p>
  </section>

  <div class="grid gap-4 md:grid-cols-2">
    {{-- Fraud: what to do right now (there is no self-service freeze yet) --}}
    <section class="flex flex-col gap-3 rounded-[24px] bg-app-panel p-5">
      <div class="flex items-center gap-3">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-500/15 text-red-600 dark:text-red-400"><x-heroicon-o-shield-exclamation class="h-5 w-5" /></span>
        <h2 class="text-base font-semibold">Report fraud</h2>
      </div>
      <p class="text-sm text-app-soft">Someone has your phone, PIN or password, or a payment you did not make? Act now:</p>
      <ol class="space-y-2 text-sm">
        <li class="flex gap-2"><span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#0A0A0C] text-[11px] font-semibold text-app-lime">1</span> <a href="{{ route('profile.edit') }}#security" class="font-semibold underline-offset-4 hover:underline">Change your password</a></li>
        <li class="flex gap-2"><span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#0A0A0C] text-[11px] font-semibold text-app-lime">2</span> <span>Email us: we can stop your account while we look into it.</span></li>
      </ol>
      <a href="mailto:support@sharet.africa?subject={{ rawurlencode('Fraud report') }}" class="mt-auto inline-flex h-12 items-center justify-center gap-2 rounded-full bg-red-600 px-5 text-[15px] font-semibold text-white hover:bg-red-700">
        <x-heroicon-o-envelope class="h-5 w-5" /> Report fraud
      </a>
    </section>

    <section class="flex flex-col gap-3 rounded-[24px] bg-app-panel p-5">
      <div class="flex items-center gap-3">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-app-send/15 text-app-send"><x-heroicon-o-chat-bubble-left-right class="h-5 w-5" /></span>
        <h2 class="text-base font-semibold">Contact us</h2>
      </div>
      <p class="text-sm text-app-soft">Write to <a href="mailto:support@sharet.africa" class="font-semibold text-app-ink underline-offset-4 hover:underline">support@sharet.africa</a>. Add the reference from your receipt if it is about a payment.</p>
      <div class="mt-auto flex flex-col gap-2 sm:flex-row">
        <a href="mailto:support@sharet.africa" class="inline-flex h-12 flex-1 items-center justify-center gap-2 rounded-full bg-gray-900 px-5 text-[15px] font-semibold text-white hover:bg-gray-800"><x-heroicon-o-envelope class="h-5 w-5" /> Email us</a>
        <a href="{{ route('contact') }}" class="inline-flex h-12 flex-1 items-center justify-center rounded-full bg-app-tile px-5 text-[15px] font-semibold text-app-ink hover:bg-app-raised">Contact page</a>
      </div>
    </section>
  </div>
</div>
@endsection
