{{-- Verification review: the automatic checks, the name on the account vs the ID, and Approve / Reject (reason shown to the person). --}}
<div id="verification" x-data="{ reason: '', busy: false, err: '',
        async decide(decision) {
          this.busy = true; this.err = '';
          try { await window.sharet.send('POST', '{{ route('users.verify', $user) }}', { decision, reason: this.reason }); this.reason = ''; await load(); }
          catch (e) { this.err = Object.values(e.errors || {})[0]?.[0] || e.body?.message || 'That did not work.'; }
          finally { this.busy = false; }
        } }">
  <x-ui.card title="Verification review" subtitle="Compare the selfie with the ID photo, then decide. The checks run by themselves when the photos are in.">
    <x-slot:actions>
      <span :class="sharet.pill({ approved: 'success', pending: 'warning', rejected: 'error' }[d.review?.status] || 'neutral')"
            x-text="{ approved: 'Verified', pending: 'Waiting for review', rejected: 'Rejected' }[d.review?.status] || (d.review?.submitted ? 'Not checked' : 'Photos missing')"></span>
    </x-slot:actions>

    <ul class="space-y-2 text-sm">
      <template x-for="c in d.review?.checks || []" :key="c.key">
        <li class="flex items-start gap-2">
          <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full" :class="c.ok ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-300'">
            <x-heroicon-s-check x-show="c.ok" class="h-3.5 w-3.5" /><x-heroicon-s-x-mark x-show="!c.ok" class="h-3.5 w-3.5" />
          </span>
          <span><span class="text-gray-900 dark:text-white" x-text="c.label"></span><span x-show="c.note" class="block text-xs text-gray-500" x-text="c.note"></span></span>
        </li>
      </template>
    </ul>

    <p x-show="d.review?.by" class="mt-4 text-xs text-gray-500"><span x-text="d.review?.by"></span> · <span x-text="d.review?.at"></span><span x-show="d.review?.reason"> · “<span x-text="d.review?.reason"></span>”</span></p>

    <div x-show="d.review?.submitted && !d.self" class="mt-4 space-y-3 border-t border-gray-100 pt-4 dark:border-gray-800">
      <x-ui.field label="Reason (needed to reject; the person sees it)" name="reason" model="reason" placeholder="The ID photo is blurred. Retake it in good light." />
      <p x-show="err" x-text="err" class="text-sm text-red-600"></p>
      <div class="flex flex-wrap gap-2">
        <x-ui.button icon="check" x-bind:disabled="busy" @click="decide('approve')">Approve</x-ui.button>
        <x-ui.button variant="secondary" icon="x-mark" x-bind:disabled="busy" @click="decide('reject')">Reject</x-ui.button>
      </div>
    </div>

    <div x-show="d.review?.history?.length" class="mt-4 border-t border-gray-100 pt-3 text-xs text-gray-500 dark:border-gray-800">
      <template x-for="h in d.review?.history || []"><p><span x-text="h.at"></span> · <span x-text="h.status"></span> by <span x-text="h.who"></span><span x-show="h.reason"> · <span x-text="h.reason"></span></span></p></template>
    </div>
  </x-ui.card>
</div>
