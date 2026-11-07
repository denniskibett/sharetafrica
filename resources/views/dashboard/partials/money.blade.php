{{-- The money panel (right-hand slide-over), one step at a time like M-PESA: Send and Withdraw go
     who → how much → review → Sharet PIN (wallet money only) → done; Receive shows your @name and QR, or adds from M-PESA.
     M-PESA options follow Daraja: B2C to a phone (send, or withdraw to your own number), B2B to a paybill or till, and
     to a bank through its paybill. Open with sharet.panel('money', { mode, to, type, dest }). All JSON, no reloads. --}}
@php
    // Sharet UI, Revolut-style: filled grey fields, pill buttons, colour only in the icons
    $input = 'h-12 w-full rounded-2xl border-0 bg-app-tile px-4 text-base text-app-ink placeholder:text-app-faint focus:outline-none focus:ring-2 focus:ring-app-ink/70';
    $label = 'mb-1.5 block text-[13px] font-medium text-app-muted';
    $err = 'mt-1 text-xs text-red-600';
    $tile = 'flex w-full items-center gap-3 rounded-2xl border-2 bg-app-tile px-3 py-2.5 text-left text-sm transition hover:bg-app-raised';
    $primary = 'h-12 w-full rounded-full bg-app-lime text-[15px] font-semibold text-[#050507] disabled:opacity-40';
    $verified = in_array($user->kyc_level, ['basic', 'standard'], true);
    $receiveUrl = route('dashboard', ['panel' => 'send', 'to' => '@'.$handle]);
    $destinations = [
        // [key, label, hint, icon, colour by meaning: Sharet lime, people blue, bills and tills violet, banks orange]
        ['sharet', 'Sharet wallet', '@name · free, instant', 'at-symbol', 'bg-app-lime text-[#050507]'],
        ['phone', 'M-PESA number', 'Any Safaricom line', 'device-phone-mobile', 'bg-app-send/15 text-app-send'],
        ['paybill', 'Paybill', 'Bills, rent, school fees', 'building-office', 'bg-app-pay/15 text-app-pay'],
        ['till', 'Till (Buy goods)', 'Shops and merchants', 'shopping-bag', 'bg-app-pay/15 text-app-pay'],
        ['bank', 'Bank account', 'Through the bank\'s paybill', 'building-library', 'bg-orange-500/15 text-orange-600 dark:text-orange-300'],
    ];
@endphp
<x-ui.slide-over name="money" title="Money" width="max-w-[720px]" x-text="({ send: 'Send money', receive: 'Receive money', withdraw: 'Withdraw' })[payload.mode] || 'Money'">
  <div x-data="moneyPanel({ urls: { quote: '{{ route('pay.quote') }}', topUp: '{{ route('pay.top-up') }}', transfer: '{{ route('pay.transfer') }}', pin: '{{ route('pay.pin') }}', base: '{{ url('pay') }}' },
                            payer: @js($user->phone), receiveUrl: @js($receiveUrl), pinSet: @js(\App\Support\TransactionPin::isSet($user)) })"
       @slide-over.window="$event.detail.name === 'money' && start($event.detail.payload || {})" class="space-y-5">

    <div x-show="step === 'who' || mode === 'receive'" class="grid grid-cols-3 gap-1 rounded-full bg-app-tile p-1 text-sm" role="tablist">
      @foreach (['send' => 'Send', 'receive' => 'Receive', 'withdraw' => 'Withdraw'] as $key => $text)
        <button type="button" role="tab" @click="setMode('{{ $key }}')" :aria-selected="mode === '{{ $key }}'"
                :class="mode === '{{ $key }}' ? 'bg-app-panel text-app-ink shadow-sm' : 'text-app-muted'" class="h-10 rounded-full font-semibold">{{ $text }}</button>
      @endforeach
    </div>

    {{-- Step dots and back, for send and withdraw --}}
    <div x-show="mode !== 'receive' && step !== 'who' && step !== 'status'" x-cloak class="flex items-center justify-between">
      <button type="button" @click="back()" class="flex items-center gap-1 text-sm font-medium text-gray-600 dark:text-gray-300"><x-heroicon-o-chevron-left class="h-5 w-5" /> Back</button>
      <ol class="flex gap-1.5" aria-label="Steps">
        <template x-for="s in ['who', 'amount', 'review', 'pin']" :key="s">
          <li class="h-1.5 w-8 rounded-full" :class="steps().indexOf(s) <= steps().indexOf(step) ? 'bg-app-ink' : 'bg-app-tile'"></li>
        </template>
      </ol>
    </div>

    @unless ($verified)
      <div x-show="mode !== 'receive' || receiveFrom === 'mpesa'" class="rounded-2xl bg-app-wait/10 p-5 text-sm">
        <p class="font-semibold text-gray-900 dark:text-white">Verify your account to move money</p>
        <p class="mt-1 text-gray-600 dark:text-gray-300">Photograph your ID (front and back) and take a quick selfie. It takes about two minutes, and we read your details from the ID.</p>
        <a href="{{ route('profile.edit') }}#verification" class="mt-4 inline-flex h-11 items-center rounded-full bg-app-lime px-5 font-semibold text-[#050507]">Verify now</a>
      </div>
    @endunless

    @if ($verified)
    {{-- 1. WHO --}}
    <div x-show="step === 'who' && mode === 'send'" class="space-y-5">
      <div x-show="recent().length">
        <p class="{{ $label }}">Recent</p>
        <div class="grid gap-2 sm:grid-cols-2">
          <template x-for="(p, i) in recent()" :key="i">
            <button type="button" @click="pick(p.payload)" class="{{ $tile }} border-transparent">
              <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-app-panel">@include('pay.partials.icon', ['expr' => 'p'])</span>
              <span class="min-w-0 truncate font-medium" x-text="p.label"></span>
            </button>
          </template>
        </div>
      </div>
      <div>
        <p class="{{ $label }}">Send to</p>
        <div class="grid gap-2 sm:grid-cols-2">
          @foreach ($destinations as [$key, $text, $hint, $icon, $tone])
            <button type="button" @click="setTo('{{ $key }}')" class="{{ $tile }}" aria-label="{{ $text }}: {{ $hint }}" :aria-pressed="to === '{{ $key }}'"
                    :class="to === '{{ $key }}' ? 'border-app-ink' : 'border-transparent'">
              <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $tone }}"><x-dynamic-component :component="'heroicon-o-'.$icon" class="h-5 w-5" /></span>
              <span class="min-w-0"><span class="block font-semibold">{{ $text }}</span><span class="block truncate text-xs text-gray-500">{{ $hint }}</span></span>
            </button>
          @endforeach
        </div>
      </div>
      <form @submit.prevent="next()" class="space-y-4">
        <div x-show="to === 'sharet'"><label for="m-handle" class="{{ $label }}">Their @name</label>
          <input id="m-handle" x-model="form.handle" autocomplete="off" autocapitalize="none" placeholder="@jane.wanjiru" class="{{ $input }}"><p class="{{ $err }}" x-text="errors.handle?.[0]"></p></div>
        <div x-show="to === 'phone'"><label for="m-phone" class="{{ $label }}">M-PESA number</label>
          <input id="m-phone" x-model="form.phone" inputmode="tel" autocomplete="tel" placeholder="0712 345 678" class="{{ $input }}"><p class="{{ $err }}" x-text="errors.phone?.[0]"></p></div>
        <div x-show="to === 'paybill'" class="grid grid-cols-2 gap-3">
          <div><label for="m-paybill" class="{{ $label }}">Paybill number</label><input id="m-paybill" x-model="form.paybill" inputmode="numeric" placeholder="888880" class="{{ $input }}"><p class="{{ $err }}" x-text="errors.paybill?.[0]"></p></div>
          <div><label for="m-account" class="{{ $label }}">Account number</label><input id="m-account" x-model="form.account" placeholder="Meter or account" class="{{ $input }}"><p class="{{ $err }}" x-text="errors.account?.[0]"></p></div>
        </div>
        <div x-show="to === 'till'"><label for="m-till" class="{{ $label }}">Till number</label>
          <input id="m-till" x-model="form.till" inputmode="numeric" placeholder="524107" class="{{ $input }}"><p class="{{ $err }}" x-text="errors.till?.[0]"></p></div>
        <div x-show="to === 'bank'">@include('dashboard.partials.money-bank')</div>
        <button type="submit" class="{{ $primary }}">Next</button>
      </form>
    </div>

    {{-- 1. WHO (withdraw): your own M-PESA or bank --}}
    <form x-show="step === 'who' && mode === 'withdraw'" @submit.prevent="next()" class="space-y-4">
      <p class="{{ $label }}">Withdraw to</p>
      <div class="grid gap-2 sm:grid-cols-2">
        <button type="button" @click="setTo('phone')" class="{{ $tile }}" :class="to === 'phone' ? 'border-app-ink' : 'border-transparent'">
          <x-heroicon-o-device-phone-mobile class="h-5 w-5" /><span><span class="block font-semibold">My M-PESA</span><span class="block text-xs text-gray-500">{{ $user->phone ?: 'Add your number first' }}</span></span></button>
        <button type="button" @click="setTo('bank')" class="{{ $tile }}" :class="to === 'bank' ? 'border-app-ink' : 'border-transparent'">
          <x-heroicon-o-building-library class="h-5 w-5" /><span><span class="block font-semibold">My bank</span><span class="block text-xs text-gray-500">Through the bank's paybill</span></span></button>
      </div>
      <div x-show="to === 'bank'">@include('dashboard.partials.money-bank')</div>
      <button type="submit" class="{{ $primary }}">Next</button>
    </form>

    {{-- 2. HOW MUCH --}}
    <form x-show="step === 'amount'" x-cloak @submit.prevent="next()" class="space-y-5">
      <div class="text-center">
        <p class="text-sm text-gray-500" x-text="mode === 'withdraw' ? 'Withdraw to ' + destLabel() : 'To ' + destLabel()"></p>
        <label for="m-amount" class="sr-only">Amount in KES</label>
        <div class="mt-3 flex items-baseline justify-center gap-2">
          <span class="text-xl font-semibold text-gray-400">KES</span>
          <input id="m-amount" x-ref="amount" x-model="form.amount" inputmode="numeric" placeholder="0" autocomplete="off"
                 class="w-48 border-0 bg-transparent p-0 text-center text-5xl font-semibold tabular-nums text-gray-900 placeholder:text-gray-300 focus:ring-0 dark:text-white">
        </div>
        <p class="{{ $err }}" x-text="errors.amount?.[0] || errors.payer?.[0]"></p>
        <div class="mt-4 flex flex-wrap justify-center gap-2">
          <template x-for="v in ['100', '500', '1,000', '2,000', '5,000']" :key="v">
            <button type="button" @click="form.amount = v" class="tabular-nums h-9 rounded-full bg-app-tile px-4 text-sm font-semibold hover:bg-app-raised" x-text="v"></button>
          </template>
        </div>
      </div>
      <fieldset x-show="mode === 'send' && to !== 'sharet'">
        <legend class="{{ $label }}">Pay from</legend>
        <div class="grid grid-cols-2 gap-2">
          <button type="button" @click="funding = 'wallet'" class="{{ $tile }}" :class="funding === 'wallet' ? 'border-app-ink' : 'border-transparent'">
            <x-heroicon-o-wallet class="h-5 w-5" /><span>Wallet <span class="block text-xs text-gray-500 tabular-nums" x-text="'KES ' + wallet"></span></span></button>
          <button type="button" @click="funding = 'mpesa'" class="{{ $tile }}" :class="funding === 'mpesa' ? 'border-app-ink' : 'border-transparent'">
            <x-heroicon-o-device-phone-mobile class="h-5 w-5" /><span>M-PESA <span class="block text-xs text-gray-500">{{ $user->phone ?: 'Add your number' }}</span></span></button>
        </div>
      </fieldset>
      <div x-show="mode === 'send'"><label for="m-note" class="{{ $label }}">Note (optional)</label><input id="m-note" x-model="form.note" maxlength="140" placeholder="Rent for November" class="{{ $input }}"></div>
      <button type="submit" :disabled="busy" class="{{ $primary }}" x-text="busy ? 'One moment…' : 'Review'"></button>
      <p class="text-center text-xs text-gray-500">Up to KES 100 is free. Above that: the M-PESA charge plus a small Sharet fee, shown next.</p>
    </form>

    {{-- 3. REVIEW --}}
    <div x-show="step === 'review'" x-cloak class="space-y-4">
      <template x-if="quote"><div x-data="{ get order() { return quote } }">@include('pay.partials.status')</div></template>
      <dl x-show="!quote" class="divide-y divide-app-line rounded-2xl bg-app-tile px-4 text-sm">
        <div class="flex justify-between py-3"><dt class="text-gray-500">To</dt><dd class="font-medium" x-text="destLabel()"></dd></div>
        <div class="flex justify-between py-3"><dt class="text-gray-500">Fee</dt><dd class="tabular-nums">Free</dd></div>
        <div class="flex justify-between py-3 font-semibold"><dt>From your wallet</dt><dd class="tabular-nums" x-text="'KES ' + form.amount"></dd></div>
      </dl>
      <p class="text-sm text-gray-500" x-text="needsPin() ? 'Next, your Sharet PIN approves this.' : 'We will send an M-PESA prompt to ' + (quote?.payer || payer) + '. Enter your M-PESA PIN there.'"></p>
      <button type="button" @click="needsPin() ? (step = 'pin') : confirm()" :disabled="busy" class="{{ $primary }}"
              x-text="busy ? 'One moment…' : (needsPin() ? 'Continue' : (quote?.top_up ? 'Add KES ' : 'Pay KES ') + (quote?.total || form.amount))"></button>
      <p class="text-xs text-red-600" x-text="errors.order?.[0] || errors.amount?.[0] || errors.handle?.[0]"></p>
    </div>

    {{-- 4. PIN (create it the first time) --}}
    <form x-show="step === 'pin'" x-cloak @submit.prevent="pinSet ? confirm() : createPin()" class="space-y-5 text-center">
      <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-app-lime/25 text-app-accent"><x-heroicon-o-lock-closed class="h-7 w-7" /></span>
      <div x-show="pinSet">
        <p class="text-lg font-semibold">Enter your Sharet PIN</p>
        <p class="text-sm text-gray-500" x-text="'Approve KES ' + (quote?.total || form.amount) + ' to ' + destLabel()"></p>
      </div>
      <div x-show="!pinSet">
        <p class="text-lg font-semibold">Create your Sharet PIN</p>
        <p class="text-sm text-gray-500">4 digits that approve money leaving your wallet. Don't share it with anyone, not even Sharet staff.</p>
      </div>
      <label for="m-pin" class="sr-only">PIN</label>
      <input id="m-pin" x-ref="pin" x-model="form.pin" type="password" inputmode="numeric" maxlength="4" autocomplete="off" placeholder="••••"
             class="mx-auto block h-14 w-44 rounded-2xl border-0 bg-app-tile text-center text-3xl tracking-[0.6em] text-app-ink focus:ring-2 focus:ring-app-ink/70">
      <div x-show="!pinSet"><label for="m-pin2" class="{{ $label }}">Type it again</label>
        <input id="m-pin2" x-model="form.pin2" type="password" inputmode="numeric" maxlength="4" autocomplete="off" placeholder="••••"
               class="mx-auto block h-14 w-44 rounded-2xl border-0 bg-app-tile text-center text-3xl tracking-[0.6em] text-app-ink focus:ring-2 focus:ring-app-ink/70"></div>
      <p class="{{ $err }}" x-text="errors.pin?.[0] || errors.pin_confirmation?.[0] || errors.amount?.[0] || errors.order?.[0]"></p>
      <button type="submit" :disabled="busy || form.pin.length !== 4" class="{{ $primary }}" x-text="busy ? 'One moment…' : (pinSet ? 'Approve' : 'Save PIN')"></button>
    </form>
    @endif

    {{-- RECEIVE: your @name and QR for other Sharet wallets; or add from your own M-PESA --}}
    <div x-show="mode === 'receive' && step === 'who'" class="space-y-5">
      <div class="grid grid-cols-2 gap-1 rounded-full bg-app-tile p-1 text-sm">
        <button type="button" @click="receiveFrom = 'sharet'" :class="receiveFrom === 'sharet' ? 'bg-app-panel text-app-ink shadow-sm' : 'text-app-muted'" class="h-9 rounded-full font-medium">From a Sharet wallet</button>
        <button type="button" @click="receiveFrom = 'mpesa'" :class="receiveFrom === 'mpesa' ? 'bg-app-panel text-app-ink shadow-sm' : 'text-app-muted'" class="h-9 rounded-full font-medium">From my M-PESA</button>
      </div>
      <div x-show="receiveFrom === 'sharet'" class="rounded-2xl bg-app-tile p-5 text-center">
        <p class="text-sm text-gray-500">Free and instant between Sharet wallets</p>
        <p class="mt-2 text-2xl font-semibold">{{ '@'.$handle }}</p>
        <canvas x-ref="qr" class="mx-auto mt-4 rounded-xl bg-white p-2" width="220" height="220" aria-label="Your QR code"></canvas>
        <p class="mt-3 text-xs text-gray-500">They scan this with their phone camera, or send to your @name.</p>
        <button type="button" @click="navigator.clipboard?.writeText('{{ '@'.$handle }}'); sharet.toast('Copied {{ '@'.$handle }}')" class="mt-3 inline-flex h-10 items-center gap-2 rounded-full bg-app-tile px-4 text-sm font-semibold hover:bg-app-raised">
          <x-heroicon-o-document-duplicate class="h-5 w-5" /> Copy my @name</button>
      </div>
      @if ($verified)
        <form x-show="receiveFrom === 'mpesa'" @submit.prevent="addMoney()" class="space-y-4 text-center">
          <p class="text-sm text-gray-500">We send a prompt to {{ $user->phone ?: 'your number (add it in Account settings)' }}. No fee.</p>
          <div class="flex items-baseline justify-center gap-2">
            <span class="text-xl font-semibold text-gray-400">KES</span>
            <input x-model="form.amount" inputmode="numeric" placeholder="0" aria-label="Amount in KES" class="w-48 border-0 bg-transparent p-0 text-center text-5xl font-semibold tabular-nums placeholder:text-gray-300 focus:ring-0 dark:text-white">
          </div>
          <p class="{{ $err }}" x-text="errors.amount?.[0] || errors.payer?.[0]"></p>
          <button type="submit" :disabled="busy" class="{{ $primary }}" x-text="busy ? 'One moment…' : 'Continue'"></button>
        </form>
      @endif
    </div>

    {{-- 5. DONE: the payment, following itself --}}
    <div x-show="step === 'status'" x-cloak>
      @include('pay.partials.status')
      <button type="button" x-show="order?.final" @click="close()" class="mt-4 h-12 w-full rounded-full bg-app-tile text-sm font-semibold hover:bg-app-raised">Done</button>
    </div>
  </div>
</x-ui.slide-over>

<script>
  function moneyPanel({ urls, payer, receiveUrl, pinSet }) {
    const blank = () => ({ handle: '', phone: '', paybill: '', till: '', account: '', bank: '', amount: '', note: '', pin: '', pin2: '' });
    return {
      mode: 'send', step: 'who', to: 'sharet', funding: 'wallet', receiveFrom: 'sharet', pinSet,
      form: blank(), errors: {}, quote: null, order: null, busy: false, timer: null,
      init() {
        // ?panel=send|receive|withdraw (&to=@name): links from other pages and scanned QR codes open the panel here
        const q = new URLSearchParams(location.search), panel = q.get('panel');
        if (!['send', 'receive', 'withdraw'].includes(panel)) return;
        history.replaceState(null, '', location.pathname); // Back then returns to the plain dashboard
        setTimeout(() => sharet.panel('money', { mode: panel, to: q.get('to') }));
      },
      start(p) {
        clearTimeout(this.timer); this.quote = null; this.order = null; this.errors = {}; this.form = blank();
        this.setMode(p.mode || 'send');
        if (p.from) this.receiveFrom = p.from; // receive: 'sharet' (QR, @name) or 'mpesa' (add money by STK)
        if (p.to) { this.to = 'sharet'; this.form.handle = p.to; }
        if (p.type) { this.to = p.type; Object.assign(this.form, p.dest || {}); }
        if (p.to || p.type) this.step = 'amount';
      },
      setMode(m) {
        this.mode = m; this.step = 'who'; this.errors = {}; this.quote = null;
        this.to = m === 'withdraw' ? 'phone' : 'sharet'; this.funding = 'wallet'; this.receiveFrom = 'sharet';
        if (m === 'receive') this.$nextTick(() => sharet.qr(this.$refs.qr, receiveUrl));
      },
      setTo(t) { this.to = t; this.errors = {}; },
      pick(payload) { this.start({ ...payload, mode: 'send' }); },
      recent() { return this.stats?.recent || []; },
      steps() { return ['who', 'amount', 'review', 'pin']; },
      // money leaving the wallet needs the Sharet PIN; M-PESA money is approved by the M-PESA PIN on the phone
      needsPin() { return this.mode !== 'add' && (this.mode === 'withdraw' || this.to === 'sharet' || this.funding === 'wallet'); },
      destLabel() {
        if (this.mode === 'withdraw') return this.to === 'bank' ? 'my bank' : 'my M-PESA';
        return ({ sharet: this.form.handle, phone: this.form.phone, paybill: 'Paybill ' + this.form.paybill + ' · ' + this.form.account,
                  till: 'Till ' + this.form.till, bank: 'Bank account ' + this.form.account })[this.to] || '';
      },
      back() {
        if (this.mode === 'add') { this.setMode('receive'); this.receiveFrom = 'mpesa'; return; }
        this.errors = {}; this.step = { amount: 'who', review: 'amount', pin: 'review' }[this.step] || 'who'; if (this.step !== 'review') this.quote = null; },
      body() {
        if (this.mode === 'withdraw') return this.to === 'bank'
          ? { type: 'bank', bank: this.form.bank, account: this.form.account, amount: this.form.amount, funding: 'wallet' }
          : { type: 'phone', phone: payer || '', amount: this.form.amount, funding: 'wallet' };
        return { ...this.form, type: this.to, funding: this.funding };
      },
      async next() {
        this.errors = {};
        if (this.step === 'who') { this.step = 'amount'; this.$nextTick(() => this.$refs.amount?.focus()); return; }
        if (this.step !== 'amount') return;
        if (this.mode === 'send' && this.to === 'sharet') { this.step = 'review'; return; } // free and instant: nothing to price
        this.busy = true;
        try { this.quote = (await sharet.send('POST', urls.quote, this.body())).order; this.step = 'review'; }
        catch (e) { this.errors = e.errors || {}; if (['phone', 'paybill', 'till', 'account', 'bank'].some(k => this.errors[k])) this.step = 'who'; }
        this.busy = false;
      },
      async addMoney() {
        this.busy = true; this.errors = {};
        try { this.quote = (await sharet.send('POST', urls.topUp, { amount: this.form.amount })).order; this.funding = 'mpesa'; this.mode = 'add'; this.step = 'review'; }
        catch (e) { this.errors = e.errors || {}; }
        this.busy = false;
      },
      async createPin() {
        this.busy = true; this.errors = {};
        try {
          await sharet.send('POST', urls.pin, { pin: this.form.pin, pin_confirmation: this.form.pin2 });
          this.pinSet = true; this.form.pin2 = '';
          await this.confirm();
        } catch (e) { this.errors = e.errors || {}; }
        this.busy = false;
      },
      async confirm() {
        this.busy = true; this.errors = {};
        try {
          if (this.mode === 'send' && this.to === 'sharet') this.order = (await sharet.send('POST', urls.transfer, this.form)).order;
          else this.order = (await sharet.send('POST', `${urls.base}/${this.quote.uuid}/confirm`, { pin: this.form.pin })).order;
          this.quote = null; this.form.pin = ''; this.step = 'status'; this.follow();
        } catch (e) {
          this.errors = e.errors || {}; this.form.pin = '';
          if (this.errors.handle) this.step = 'who';
          else if (this.errors.amount && !this.errors.pin) this.step = 'amount';
        }
        this.busy = false;
      },
      follow() {
        clearTimeout(this.timer); window.dispatchEvent(new CustomEvent('wallet-changed'));
        if (!this.order || this.order.final) return;
        this.timer = setTimeout(async () => {
          try {
            const res = await fetch(this.order.url, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
            if (res.ok) this.order = (await res.json()).order;
          } catch (e) {}
          this.follow();
        }, 2000);
      },
    };
  }
</script>
