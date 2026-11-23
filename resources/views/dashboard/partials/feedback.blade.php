{{-- "How was that?": asked once, after the first payment out went through (App\Support\Funnel::askFeedback).
     1–5, then an optional note; "Not now" counts as answered so it never nags. Admin › Website analytics shows the results. --}}
<section x-data="{ score: null, comment: '', sent: false, gone: false,
           async send(score) { this.sent = true; await fetch(@js(route('feedback')), { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }, body: JSON.stringify({ score, comment: this.comment }) }) } }"
         x-show="!gone" x-transition class="rounded-2xl border border-app-line bg-app-panel p-5" aria-labelledby="fb-h">
  <template x-if="!sent">
    <div>
      <div class="flex items-start gap-3">
        <span class="sh-tone-pay flex h-11 w-11 shrink-0 items-center justify-center rounded-[14px]"><x-heroicon-o-chat-bubble-left-ellipsis class="h-[22px] w-[22px]" /></span>
        <div class="min-w-0 flex-1">
          <h2 id="fb-h" class="font-semibold">Your first payment went through. How was that?</h2>
          <p class="text-sm text-app-muted">One tap. It helps us make Sharet easier.</p>
        </div>
        <button type="button" @click="send(null); gone = true" class="shrink-0 text-sm text-app-muted hover:text-app-ink">Not now</button>
      </div>
      <div class="mt-4 grid grid-cols-5 gap-2" role="radiogroup" aria-label="Score from 1, hard, to 5, easy">
        @foreach ([1 => 'Hard', 2 => '', 3 => 'OK', 4 => '', 5 => 'Easy'] as $n => $word)
          <button type="button" role="radio" :aria-checked="score === {{ $n }}" @click="score = {{ $n }}"
                  class="flex h-14 flex-col items-center justify-center rounded-[14px] border text-base font-semibold transition"
                  :class="score === {{ $n }} ? 'border-[#0A0A0C] bg-[#0A0A0C] text-white' : 'border-app-line hover:border-app-ink/40'">
            {{ $n }}@if ($word)<span class="text-[10.5px] font-normal opacity-70">{{ $word }}</span>@endif
          </button>
        @endforeach
      </div>
      <div x-show="score" x-collapse x-cloak class="mt-3 flex flex-col gap-2 sm:flex-row">
        <input type="text" x-model="comment" maxlength="1000" placeholder="Anything we should fix? (optional)" class="h-12 flex-1 px-4 text-sm" aria-label="Anything we should fix?">
        <button type="button" @click="send(score)" class="h-12 rounded-[14px] bg-[#0A0A0C] px-6 text-sm font-semibold text-white">Send</button>
      </div>
    </div>
  </template>
  <p x-show="sent" x-cloak class="flex items-center gap-2 text-sm font-medium"><x-heroicon-o-check-circle class="h-5 w-5 text-app-in" /> Thank you. We read every note.</p>
</section>
