{{-- The dashboard's single centre alert modal. Opened by sharet.alert() / sharet.confirm() (resources/js/dashboard.js). --}}
<div x-data="alertModal()" @alert-modal.window="show($event.detail)" @keydown.escape.window="open && close(false)"
     x-show="open" x-cloak class="fixed inset-0 z-[100002] flex items-center justify-center p-4" role="alertdialog" aria-modal="true" :aria-label="title">
  <div x-show="open" x-transition.opacity class="absolute inset-0 bg-black/50" @click="close(false)"></div>
  <div x-show="open" x-transition x-trap.noscroll="open" class="relative w-full max-w-md rounded-2xl bg-white p-6 text-center shadow-xl dark:bg-gray-900">
    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full"
          :class="{ 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400': tone === 'success', 'bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400': tone === 'warning', 'bg-red-100 text-red-600 dark:bg-red-500/15 dark:text-red-400': tone === 'error', 'bg-sky-100 text-sky-600 dark:bg-sky-500/15 dark:text-sky-400': tone === 'info' }">
      <x-heroicon-o-check-circle x-show="tone === 'success'" class="h-7 w-7" />
      <x-heroicon-o-exclamation-triangle x-show="tone === 'warning'" class="h-7 w-7" />
      <x-heroicon-o-x-circle x-show="tone === 'error'" class="h-7 w-7" />
      <x-heroicon-o-information-circle x-show="tone === 'info'" class="h-7 w-7" />
    </span>
    <h2 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white" x-text="title"></h2>
    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400" x-text="message"></p>
    <div class="mt-6 flex justify-center gap-3">
      <button type="button" x-show="cancel" @click="close(false)" class="h-10 rounded-lg border border-gray-300 px-5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5" x-text="cancelText"></button>
      <button type="button" x-ref="confirm" @click="close(true)" class="h-10 rounded-lg px-5 text-sm font-medium"
              :class="tone === 'error' ? 'bg-red-600 text-white hover:bg-red-700' : 'bg-gray-900 text-white hover:bg-gray-800 dark:bg-[#D4FF3D] dark:text-gray-900'" x-text="confirmText"></button>
    </div>
  </div>
</div>
