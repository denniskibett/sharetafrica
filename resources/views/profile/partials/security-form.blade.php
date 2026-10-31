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
