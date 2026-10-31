{{-- Account level: Verified (ID and selfies) → bank grade; business accounts point to Business settings. --}}
<x-ui.card title="Verification" subtitle="More details unlock more services. Everything you give us is encrypted.">
  <ol class="space-y-4">
    <li class="rounded-xl border p-4" :class="data.level.basic.complete ? 'border-emerald-200 bg-emerald-50/50 dark:border-emerald-500/30 dark:bg-emerald-500/5' : 'border-gray-200 dark:border-gray-800'">
      <div class="flex items-center justify-between gap-3">
        <div>
          <p class="font-medium text-gray-900 dark:text-white">Verified</p>
          <p class="text-xs text-gray-500 dark:text-gray-400">M-Pesa number, ID photos and three selfies. Send and receive at basic limits.</p>
        </div>
        <span :class="sharet.pill(data.level.basic.complete ? 'success' : 'warning')" x-text="data.level.basic.complete ? 'Done' : data.level.basic.done + ' of ' + data.level.basic.of"></span>
      </div>
      <div class="mt-3 h-1.5 rounded-full bg-gray-100 dark:bg-white/5"><div class="h-1.5 rounded-full bg-emerald-500" :style="`width: ${percent(data.level.basic)}%`"></div></div>
    </li>
    <li class="rounded-xl border p-4" :class="data.level.standard.complete ? 'border-emerald-200 bg-emerald-50/50 dark:border-emerald-500/30 dark:bg-emerald-500/5' : 'border-gray-200 dark:border-gray-800'">
      <div class="flex items-center justify-between gap-3">
        <div>
          <p class="font-medium text-gray-900 dark:text-white">Bank grade</p>
          <p class="text-xs text-gray-500 dark:text-gray-400">ID, KRA PIN, address, income, ID photos, selfies and signature. Loans, cards, trade and registering a business.</p>
        </div>
        <span :class="sharet.pill(data.level.standard.complete ? 'success' : 'neutral')" x-text="data.level.standard.complete ? 'Done' : data.level.standard.done + ' of ' + data.level.standard.of"></span>
      </div>
      <div class="mt-3 h-1.5 rounded-full bg-gray-100 dark:bg-white/5"><div class="h-1.5 rounded-full bg-gray-900 dark:bg-[#D4FF3D]" :style="`width: ${percent(data.level.standard)}%`"></div></div>
    </li>
    <li x-show="data.user.has_company" class="rounded-xl border border-gray-200 p-4 dark:border-gray-800">
      <div class="flex items-center justify-between gap-3">
        <div>
          <p class="font-medium text-gray-900 dark:text-white">Business · bank grade</p>
          <p class="text-xs text-gray-500 dark:text-gray-400">For directors with a personal account. Registration, directors, KRA PIN and settlement account.</p>
        </div>
        <x-ui.button variant="secondary" class="!h-9" href="{{ route('settings.company') }}">Open</x-ui.button>
      </div>
    </li>
  </ol>
  <p class="mt-4 text-xs text-gray-500" x-show="data.level.submitted">Last sent for review on <span x-text="data.level.submitted"></span>.</p>
  <div class="mt-5"><x-ui.button icon="identification" href="{{ route('profile.edit') }}#verification">Update verification</x-ui.button></div>
</x-ui.card>
