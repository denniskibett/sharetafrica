{{-- Kill switches (super admins). Each change asks for the password again and a reason, and is written to the security log. --}}
<x-ui.card title="Kill switches" subtitle="For emergencies. Each change needs your password and is written to the security log. On the server: php artisan sharet:lockdown." class="mb-6"
    x-data="{ ls: { switch: '', on: false, reason: '', password: '' }, lsBusy: false, lsErr: {},
              lsOpen(sw) { this.ls = { switch: sw, on: !d.lockdown[sw], reason: '', password: '' }; this.lsErr = {}; sharet.panel('lockdown') },
              async lsSave() { this.lsBusy = true; this.lsErr = {};
                try { const r = await sharet.send('POST', '{{ route('system.lockdown') }}', this.ls); d.lockdown = r.lockdown; window.dispatchEvent(new CustomEvent('slide-over-close')) }
                catch (e) { this.lsErr = Object.fromEntries(Object.entries(e.errors || {}).map(([k, v]) => [k, v[0]])) }
                this.lsBusy = false } }">
  <div class="divide-y divide-gray-100 dark:divide-gray-800">
    @foreach (['ledger' => ['Ledger freeze', 'No money moves: payments, top-ups, transfers and payouts stop. Balances still show. M-Pesa callbacks are kept and booked when it reopens.', 'warning'],
               'system' => ['System lockdown', 'Everyone is shut out of sharet.africa, the dashboard and the API, and the ledger freezes. Only super admins on an allowed IP get in.', 'error']] as $sw => [$label, $hint, $tone])
      <div class="flex flex-wrap items-start justify-between gap-4 py-4 first:pt-0 last:pb-0">
        <div class="min-w-0 max-w-xl">
          <div class="flex items-center gap-2">
            <h4 class="font-semibold text-gray-900 dark:text-white">{{ $label }}</h4>
            <span :class="sharet.pill(d.lockdown?.{{ $sw }} ? '{{ $tone }}' : 'success')" x-text="d.lockdown?.{{ $sw }} ? 'On' : 'Off'"></span>
          </div>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $hint }}</p>
          <p x-show="d.lockdown?.{{ $sw }} && d.lockdown?.{{ $sw }}_reason" class="mt-2 text-sm text-gray-700 dark:text-gray-300"
             x-text="'“' + d.lockdown?.{{ $sw }}_reason + '” · ' + (d.lockdown?.{{ $sw }}_by || '') + ' · ' + (d.lockdown?.{{ $sw }}_at || '')"></p>
          <p x-show="d.lockdown?.{{ $sw }}_env" class="mt-2 text-sm text-amber-600">Forced on in .env: only the server can lift it.</p>
        </div>
        <x-ui.button :variant="$sw === 'system' ? 'danger' : 'secondary'" icon="{{ $sw === 'system' ? 'lock-closed' : 'pause-circle' }}"
            x-show="d.lockdown?.can && !d.lockdown?.{{ $sw }}_env" @click="lsOpen('{{ $sw }}')"
            x-text="d.lockdown?.{{ $sw }} ? 'Turn off' : 'Turn on'"></x-ui.button>
      </div>
    @endforeach
  </div>
  <p x-show="d.lockdown?.can && !d.lockdown?.break_glass" class="mt-4 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-800 dark:bg-amber-500/10 dark:text-amber-300">
    Your IP is not in LOCKDOWN_ALLOW_IPS, so once the system is locked you can only reopen it from the server.
  </p>

  <x-ui.slide-over name="lockdown" title="Confirm the switch">
    <div class="space-y-5" @keydown.enter.prevent="lsSave()">
      <p class="text-sm text-gray-600 dark:text-gray-300"
         x-text="(ls.on ? 'Turn ON ' : 'Turn OFF ') + (ls.switch === 'system' ? 'the system lockdown.' : 'the ledger freeze.')"></p>
      <x-ui.field label="Reason" name="reason" x-show="ls.on">
        <textarea x-model="ls.reason" rows="3" maxlength="300" class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900"></textarea>
        <p x-show="lsErr.reason" x-text="lsErr.reason" class="mt-1 text-sm text-error-500"></p>
      </x-ui.field>
      <x-ui.field label="Your password" name="password">
        <input type="password" x-model="ls.password" autocomplete="current-password" required class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900">
        <p x-show="lsErr.password" x-text="lsErr.password" class="mt-1 text-sm text-error-500"></p>
      </x-ui.field>
      <x-ui.button variant="danger" @click="lsSave()" x-bind:disabled="lsBusy" x-text="ls.on ? 'Turn on' : 'Turn off'"></x-ui.button>
    </div>
  </x-ui.slide-over>
</x-ui.card>
