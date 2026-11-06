{{-- Close account. Plain form post (signs out and returns to the home page). --}}
<x-ui.card title="Close your account" subtitle="Move any balance out first. We keep transaction records for as long as the law requires.">
  <form method="POST" action="{{ route('profile.destroy') }}" class="max-w-xl space-y-5"
        @submit.prevent="sharet.confirm({ title: 'Close your Sharet account?', message: 'You will be signed out and lose access straight away.', tone: 'error', confirmText: 'Close account' }).then(ok => ok && $el.submit())">
    @csrf
    @method('DELETE')
    <div>
      <label for="close-password" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Your password</label>
      <input id="close-password" name="password" type="password" required autocomplete="current-password" class="h-12 w-full px-4 ui-input rounded-2xl border-0 bg-gray-100 text-[15px] focus:outline-none focus:ring-2 focus:ring-gray-900/70 dark:bg-white/[0.06] dark:text-white/90">
      @error('password', 'userDeletion')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <x-ui.button type="submit" variant="danger" icon="x-circle">Close account</x-ui.button>
  </form>
</x-ui.card>
