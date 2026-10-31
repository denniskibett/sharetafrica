{{-- Maker then checker. Reads the day's state from the table's stats (dataTable scope). --}}
<div class="mb-6 flex flex-col gap-3 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:flex-row sm:items-center sm:justify-between">
  <div class="text-sm">
    <p class="font-medium text-gray-800 dark:text-white/90" x-text="stats.pill?.label"></p>
    <p class="text-gray-500 dark:text-gray-400">
      <span x-show="stats.status === 'open' && stats.breaks > 0">Fix or park every break, then prepare the day.</span>
      <span x-show="stats.status === 'open' && stats.breaks === 0">Everything matches. Prepare the day; a different person then signs it off.</span>
      <span x-show="stats.status === 'prepared'">Prepared by <span x-text="stats.prepared_by"></span>. Waiting for a different person to sign it off.</span>
      <span x-show="stats.status === 'signed_off'">Prepared by <span x-text="stats.prepared_by"></span>, signed off by <span x-text="stats.signed_off_by"></span>.</span>
    </p>
  </div>
  <div class="flex gap-2">
    <button type="button" x-show="stats.status === 'open'" :disabled="stats.breaks > 0"
            @click="recon.act('{{ route('ops.reconciliation.prepare', $dayId) }}', 'Confirm every line is matched or explained?')"
            class="h-10 whitespace-nowrap rounded-lg bg-gray-900 px-4 text-sm font-medium text-white disabled:opacity-40 dark:bg-[#D4FF3D] dark:text-gray-900">Prepare day</button>
    <button type="button" x-show="stats.status === 'prepared'" :disabled="!stats.can_sign"
            @click="recon.act('{{ route('ops.reconciliation.sign-off', $dayId) }}', 'Sign off this day as checked?')"
            class="h-10 whitespace-nowrap rounded-lg bg-gray-900 px-4 text-sm font-medium text-white disabled:opacity-40 dark:bg-[#D4FF3D] dark:text-gray-900"
            :title="stats.can_sign ? '' : 'You prepared this day: someone else must sign it off'">Sign off</button>
  </div>
</div>
