{{-- Change password (PUT /profile/password). --}}
<x-ui.card title="Password" subtitle="At least 8 characters with letters and numbers. Don't reuse your M-Pesa PIN or bank password.">
  <form x-data="{ pw: { current_password: '', password: '', password_confirmation: '' } }"
        @submit.prevent="submit('PUT', '{{ route('profile.password') }}', pw).then(r => r && (pw = { current_password: '', password: '', password_confirmation: '' }))" class="max-w-xl space-y-5">
    <x-ui.field label="Current password" name="current_password" type="password" model="pw.current_password" required autocomplete="current-password" />
    <x-ui.field label="New password" name="password" type="password" model="pw.password" required autocomplete="new-password" />
    <x-ui.field label="Confirm new password" name="password_confirmation" type="password" model="pw.password_confirmation" required autocomplete="new-password" />
    <x-ui.button type="submit" icon="lock-closed" x-bind:disabled="busy">Change password</x-ui.button>
  </form>
</x-ui.card>

{{-- Sharet PIN (POST pay.pin): approves wallet payments, withdrawals and @name transfers. Changing it needs the current one. --}}
<x-ui.card class="mt-6" title="Sharet PIN" subtitle="4 digits that approve money leaving your wallet. Not your M-Pesa PIN, and Sharet never asks for it outside the app or a pay page.">
  <form x-data="{ p: { current: '', pin: '', pin_confirmation: '' } }"
        @submit.prevent="submit('POST', '{{ route('pay.pin') }}', p).then(r => r && (p = { current: '', pin: '', pin_confirmation: '' }))" class="max-w-xl space-y-5">
    <p class="text-sm text-gray-500 dark:text-gray-400" x-text="data.user?.pin_set ? 'Your PIN is set. Enter it to change it.' : 'You have not set a PIN yet.'"></p>
    <template x-if="data.user?.pin_set">
      <x-ui.field label="Current PIN" name="current" type="password" model="p.current" inputmode="numeric" maxlength="4" autocomplete="off" />
    </template>
    <x-ui.field label="New PIN" name="pin" type="password" model="p.pin" inputmode="numeric" maxlength="4" required autocomplete="new-password" />
    <x-ui.field label="Type it again" name="pin_confirmation" type="password" model="p.pin_confirmation" inputmode="numeric" maxlength="4" required autocomplete="new-password" />
    <x-ui.button type="submit" icon="key" x-bind:disabled="busy">Save PIN</x-ui.button>
  </form>
</x-ui.card>
