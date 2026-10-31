{{-- Pay form: who to pay, how much, then the quote and confirm. Part of the payPage() component in pay/index. --}}
@php
    $input = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 placeholder:text-gray-400 focus:border-violet-400 focus:outline-none focus:ring-4 focus:ring-violet-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $label = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
    $err = 'mt-1 text-xs text-red-600';
@endphp
<form @submit.prevent="getQuote()" class="space-y-4" x-show="!quote">
  <div class="grid grid-cols-4 gap-1 rounded-lg bg-gray-100 p-1 text-sm dark:bg-white/5" role="tablist">
    @foreach (['phone' => 'Phone', 'paybill' => 'Paybill', 'till' => 'Till', 'bank' => 'Bank'] as $key => $text)
      <button type="button" role="tab" @click="type = '{{ $key }}'; errors = {}" :aria-selected="type === '{{ $key }}'"
              :class="type === '{{ $key }}' ? 'bg-white text-gray-900 shadow-sm dark:bg-gray-800 dark:text-white' : 'text-gray-500'"
              class="h-9 rounded-md font-medium">{{ $text }}</button>
    @endforeach
  </div>

  <div x-show="type === 'phone'">
    <label for="pay-phone" class="{{ $label }}">M-PESA number</label>
    <input id="pay-phone" x-model="form.phone" inputmode="tel" autocomplete="tel" placeholder="0712 345 678" class="{{ $input }}">
    <p class="{{ $err }}" x-text="errors.phone?.[0]"></p>
  </div>
  <div x-show="type === 'paybill'" class="grid grid-cols-2 gap-3">
    <div><label for="pay-paybill" class="{{ $label }}">Paybill number</label>
      <input id="pay-paybill" x-model="form.paybill" inputmode="numeric" placeholder="888880" class="{{ $input }}"><p class="{{ $err }}" x-text="errors.paybill?.[0]"></p></div>
    <div><label for="pay-account" class="{{ $label }}">Account number</label>
      <input id="pay-account" x-model="form.account" placeholder="Meter or account" class="{{ $input }}"><p class="{{ $err }}" x-text="errors.account?.[0]"></p></div>
  </div>
  <div x-show="type === 'till'">
    <label for="pay-till" class="{{ $label }}">Till number</label>
    <input id="pay-till" x-model="form.till" inputmode="numeric" placeholder="524107" class="{{ $input }}">
    <p class="{{ $err }}" x-text="errors.till?.[0]"></p>
  </div>
  <div x-show="type === 'bank'" class="grid grid-cols-2 gap-3">
    <div><label for="pay-bank" class="{{ $label }}">Bank</label>
      <select id="pay-bank" x-model="form.bank" class="{{ $input }}">
        <option value="">Choose a bank</option>
        @foreach ($banks as $code => $name)<option value="{{ $code }}">{{ $name }}</option>@endforeach
      </select><p class="{{ $err }}" x-text="errors.bank?.[0]"></p></div>
    <div><label for="pay-bank-account" class="{{ $label }}">Account number</label>
      <input id="pay-bank-account" x-model="form.account" inputmode="numeric" class="{{ $input }}"><p class="{{ $err }}" x-text="errors.account?.[0]"></p></div>
  </div>

  <div class="grid grid-cols-2 gap-3">
    <div><label for="pay-amount" class="{{ $label }}">Amount (KES)</label>
      <input id="pay-amount" x-model="form.amount" inputmode="numeric" placeholder="1,500" class="{{ $input }} tabular-nums"><p class="{{ $err }}" x-text="errors.amount?.[0]"></p></div>
    <div><label for="pay-note" class="{{ $label }}">Note (optional)</label>
      <input id="pay-note" x-model="form.note" maxlength="140" placeholder="Rent for November" class="{{ $input }}"></div>
  </div>
  <p class="{{ $err }}" x-text="errors.payer?.[0]"></p>

  <button type="submit" :disabled="busy" class="inline-flex h-11 w-full items-center justify-center rounded-lg bg-gray-900 text-sm font-medium text-white hover:bg-gray-800 disabled:opacity-50 dark:bg-[#D4FF3D] dark:text-gray-900">
    <span x-text="busy ? 'Working out the cost…' : 'See the cost'"></span>
  </button>
</form>

<div x-show="quote" x-cloak class="space-y-4">
  <template x-if="quote"><div x-data="{ get order() { return quote } }">@include('pay.partials.status')</div></template>
  <p class="text-sm text-gray-500">We will send an M-PESA prompt to <span class="font-medium text-gray-800 dark:text-gray-200" x-text="quote?.payer"></span>. This quote is held for {{ config('pricing.quote_minutes') }} minutes.</p>
  <div class="flex gap-3">
    <button type="button" @click="quote = null" class="h-11 flex-1 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 dark:border-gray-700 dark:text-gray-300">Change</button>
    <button type="button" @click="confirm()" :disabled="busy" class="h-11 flex-1 rounded-lg bg-gray-900 text-sm font-medium text-white disabled:opacity-50 dark:bg-[#D4FF3D] dark:text-gray-900"
            x-text="busy ? 'Sending prompt…' : 'Pay KES ' + quote?.total"></button>
  </div>
  <p class="text-xs text-red-600" x-text="errors.order?.[0] || errors.amount?.[0]"></p>
</div>
