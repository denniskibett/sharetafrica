{{-- One journal in the slide-over. Open with ledger.openJournal(url): it fetches the JSON and calls sharet.panel('journal', data). --}}
<x-ui.slide-over name="journal" title="Journal" x-text="payload.type ? 'Journal · ' + payload.type : 'Journal'">
  <div class="space-y-5 text-sm">
    <dl class="grid grid-cols-[110px_1fr] gap-x-4 gap-y-2">
      <dt class="text-gray-500">Posted</dt><dd class="text-gray-800 dark:text-gray-200" x-text="payload.posted_at"></dd>
      <dt class="text-gray-500">Memo</dt><dd class="text-gray-800 dark:text-gray-200" x-text="payload.memo || '—'"></dd>
      <dt class="text-gray-500">Source</dt><dd class="text-gray-800 dark:text-gray-200" x-text="payload.source || '—'"></dd>
      <dt class="text-gray-500">Key</dt><dd class="break-all font-mono text-xs text-gray-600 dark:text-gray-400" x-text="payload.key"></dd>
      <dt class="text-gray-500" x-show="payload.reverses">Reverses</dt><dd class="font-mono text-xs" x-show="payload.reverses" x-text="payload.reverses"></dd>
      <dt class="text-gray-500" x-show="payload.reversed_by">Reversed by</dt><dd class="font-mono text-xs" x-show="payload.reversed_by" x-text="payload.reversed_by"></dd>
    </dl>
    <table class="w-full text-left">
      <thead><tr class="border-b border-gray-100 text-xs font-medium text-gray-500 dark:border-gray-800"><th class="py-2">Account</th><th class="py-2 text-right">Debit</th><th class="py-2 text-right">Credit</th></tr></thead>
      <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
        <template x-for="(line, i) in payload.lines || []" :key="i">
          <tr><td class="py-2"><a :href="line.account_url" class="text-gray-800 hover:underline dark:text-gray-200" x-text="line.account"></a></td>
            <td class="py-2 text-right tabular-nums" x-text="line.debit"></td><td class="py-2 text-right tabular-nums" x-text="line.credit"></td></tr>
        </template>
      </tbody>
      <tfoot><tr class="border-t border-gray-200 font-medium dark:border-gray-700"><td class="py-2">Total</td><td class="py-2 text-right tabular-nums" x-text="payload.total"></td><td class="py-2 text-right tabular-nums" x-text="payload.total"></td></tr></tfoot>
    </table>
    <p class="break-all font-mono text-[11px] text-gray-400">Hash <span x-text="payload.hash"></span></p>
  </div>
</x-ui.slide-over>

<script>
  window.ledger = {
    async openJournal(url) {
      const res = await fetch(url, { headers: { Accept: 'application/json' } });
      if (res.ok) sharet.panel('journal', await res.json());
    },
  };
</script>
