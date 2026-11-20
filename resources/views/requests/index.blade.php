{{-- Requests and splits (canvas P05). Ask an @name or any phone for money; they get an SMS link (/r/{token}) and pay by
     M-PESA prompt or their Sharet wallet with their PIN. Split a bill: one request each. Nothing moves until the payer approves. --}}
@extends('layouts.app')
@section('title', 'Requests')

@php
  $input = 'h-12 w-full rounded-2xl border-0 bg-app-tile px-4 text-base text-app-ink placeholder:text-app-faint focus:outline-none focus:ring-2 focus:ring-app-ink/70';
  $label = 'mb-1.5 block text-[13px] font-medium text-app-muted';
  $primary = 'h-12 rounded-full bg-app-lime px-6 text-[15px] font-semibold text-[#050507] disabled:opacity-40';
  $soft = 'h-10 rounded-full bg-app-tile px-4 text-sm font-semibold hover:bg-app-raised';
@endphp

@section('content')
<div class="mx-auto max-w-[1000px] space-y-6"
     x-data="{ tab: 'to_pay', mode: null, data: { to_pay: [], sent: [] }, busy: false, errors: {}, msg: '',
               form: { to: '', amount: '', note: '', expires: '48h' }, split: { total: '', people: '', note: '', include_me: true },
               async load() { this.data = await (await fetch('{{ route('requests.data') }}', { headers: { Accept: 'application/json' } })).json() },
               async post(url, body, done) {
                 this.busy = true; this.errors = {};
                 try { const r = await window.sharet.send('POST', url, body || {}); sharet.toast(r.message); done && done(r); await this.load(); }
                 catch (e) { this.errors = e.errors || { form: [e.body?.message || 'That did not work.'] }; if (!e.errors) sharet.toast(this.errors.form[0], 'error'); }
                 finally { this.busy = false } },
               label(s) { return { open: 'Waiting', paid: 'Paid', declined: 'Declined', expired: 'Expired', cancelled: 'Cancelled' }[s] || s },
               tone(s) { return { open: 'warning', paid: 'success', declined: 'error' }[s] || 'neutral' } }"
     x-init="load()">
  <div class="flex flex-wrap items-end justify-between gap-4">
    <div>
      <h1 class="text-2xl font-semibold text-app-ink">Requests</h1>
      <p class="mt-1 text-sm text-app-muted">Ask anyone for money or split a bill. They get a link: Sharet members pay free from their wallet, anyone else by M-PESA.</p>
    </div>
    <div class="flex gap-2">
      <button type="button" @click="mode = mode === 'ask' ? null : 'ask'" class="{{ $primary }}">Request money</button>
      <button type="button" @click="mode = mode === 'split' ? null : 'split'" class="{{ $soft }} h-12 px-5">Split a bill</button>
    </div>
  </div>

  {{-- Request money --}}
  <form x-show="mode === 'ask'" x-cloak @submit.prevent="post('{{ route('requests.store') }}', form, () => { form = { to: '', amount: '', note: '', expires: '48h' }; mode = null })"
        class="grid gap-4 rounded-3xl bg-app-panel p-5 md:grid-cols-2">
    <div><label for="r-to" class="{{ $label }}">From (@name or phone)</label><input id="r-to" x-model="form.to" class="{{ $input }}" placeholder="@akinyi or 0722 410 556" required><p class="mt-1 text-xs text-red-600" x-text="errors.to?.[0]"></p></div>
    <div><label for="r-amount" class="{{ $label }}">Amount (KES)</label><input id="r-amount" x-model="form.amount" inputmode="numeric" class="{{ $input }}" placeholder="600" required><p class="mt-1 text-xs text-red-600" x-text="errors.amount?.[0]"></p></div>
    <div><label for="r-note" class="{{ $label }}">What for</label><input id="r-note" x-model="form.note" maxlength="140" class="{{ $input }}" placeholder="Lunch on Friday"></div>
    <div><span class="{{ $label }}">Expires in</span>
      <div class="flex gap-2">@foreach (['48h' => '48 hours', '7d' => '7 days'] as $k => $t)<button type="button" @click="form.expires = '{{ $k }}'" :class="form.expires === '{{ $k }}' ? 'bg-app-ink text-white' : 'bg-app-tile'" class="h-12 flex-1 rounded-full text-sm font-semibold">{{ $t }}</button>@endforeach</div></div>
    <div class="md:col-span-2"><button :disabled="busy" class="{{ $primary }} w-full" x-text="busy ? 'Sending…' : 'Send request'"></button></div>
  </form>

  {{-- Split a bill --}}
  <form x-show="mode === 'split'" x-cloak @submit.prevent="post('{{ route('requests.split') }}', { total: split.total, note: split.note, include_me: split.include_me, people: split.people.split(/[\n,]+/).map(s => s.trim()).filter(Boolean) }, () => { split = { total: '', people: '', note: '', include_me: true }; mode = null; tab = 'sent' })"
        class="grid gap-4 rounded-3xl bg-app-panel p-5 md:grid-cols-2">
    <div><label for="s-total" class="{{ $label }}">Total bill (KES)</label><input id="s-total" x-model="split.total" inputmode="numeric" class="{{ $input }}" placeholder="4,800" required><p class="mt-1 text-xs text-red-600" x-text="errors.amount?.[0]"></p></div>
    <div><label for="s-note" class="{{ $label }}">What for</label><input id="s-note" x-model="split.note" maxlength="140" class="{{ $input }}" placeholder="Dinner at Java"></div>
    <div class="md:col-span-2"><label for="s-people" class="{{ $label }}">With (one @name or phone per line)</label>
      <textarea id="s-people" x-model="split.people" rows="3" class="w-full rounded-2xl border-0 bg-app-tile px-4 py-3 text-base focus:ring-2 focus:ring-app-ink/70" placeholder="@akinyi&#10;0733 112 908" required></textarea>
      <p class="mt-1 text-xs text-red-600" x-text="errors.people?.[0] || errors.to?.[0]"></p></div>
    <label class="flex items-center gap-3 text-sm md:col-span-2"><input type="checkbox" x-model="split.include_me" class="h-5 w-5 rounded"> Include my share (I paid; I don't ask myself)</label>
    <div class="md:col-span-2"><button :disabled="busy" class="{{ $primary }} w-full" x-text="busy ? 'Sending…' : 'Send the split'"></button></div>
  </form>

  <div class="grid grid-cols-2 gap-1 rounded-full bg-app-tile p-1 text-sm sm:w-80" role="tablist">
    <button type="button" role="tab" @click="tab = 'to_pay'" :aria-selected="tab === 'to_pay'" :class="tab === 'to_pay' ? 'bg-app-panel text-app-ink shadow-sm' : 'text-app-muted'" class="h-10 rounded-full font-semibold" x-text="'To pay (' + data.to_pay.filter(r => r.status === 'open').length + ')'"></button>
    <button type="button" role="tab" @click="tab = 'sent'" :aria-selected="tab === 'sent'" :class="tab === 'sent' ? 'bg-app-panel text-app-ink shadow-sm' : 'text-app-muted'" class="h-10 rounded-full font-semibold" x-text="'Sent (' + data.sent.length + ')'"></button>
  </div>

  <ul class="divide-y divide-app-line overflow-hidden rounded-3xl bg-app-panel">
    <template x-for="r in data[tab]" :key="r.id">
      <li class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center">
        <div class="min-w-0 flex-1">
          <p class="truncate font-semibold" x-text="(tab === 'to_pay' ? r.who.split(' · ')[0] + ' asks KES ' : r.who + ' · KES ') + r.amount"></p>
          <p class="truncate text-sm text-app-muted" x-text="[r.note, r.left, r.when, r.group ? 'split' : null, r.id].filter(Boolean).join(' · ')"></p>
        </div>
        <span :class="sharet.pill(tone(r.status))" x-text="label(r.status)"></span>
        <div class="flex gap-2" x-show="r.status === 'open'">
          <template x-if="tab === 'to_pay'">
            <div class="flex gap-2"><button type="button" @click="post('{{ url('requests') }}/' + r.id + '/decline')" class="{{ $soft }}">Decline</button><a :href="r.pay_url" class="inline-flex h-10 items-center rounded-full bg-app-lime px-4 text-sm font-semibold text-[#050507]" x-text="'Pay KES ' + r.amount"></a></div>
          </template>
          <template x-if="tab === 'sent'">
            <div class="flex gap-2">
              <button type="button" @click="navigator.clipboard?.writeText(r.pay_url); sharet.toast('Payment link copied')" class="{{ $soft }}">Copy link</button>
              <button type="button" @click="post('{{ url('requests') }}/' + r.id + '/remind')" class="{{ $soft }}">Remind</button>
              <button type="button" @click="post('{{ url('requests') }}/' + r.id + '/cancel')" class="{{ $soft }}">Cancel</button>
            </div>
          </template>
        </div>
      </li>
    </template>
    <li x-show="!data[tab].length" class="p-10 text-center text-sm text-app-muted" x-text="tab === 'to_pay' ? 'Nobody has asked you for money.' : 'You haven\'t asked anyone yet.'"></li>
  </ul>
</div>
@endsection
