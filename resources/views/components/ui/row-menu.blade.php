{{-- "⋯" actions menu for a table row. Fixed-positioned so the table's horizontal scroll never clips it.
     Children: <x-ui.row-menu-item>. --}}
@props(['label' => 'Actions'])
<div x-data="{ open: false, style: '', toggle() { if (this.open) return this.open = false; const r = this.$refs.btn.getBoundingClientRect();
        const below = innerHeight - r.bottom > 220; this.style = `right:${innerWidth - r.right}px;` + (below ? `top:${r.bottom + 6}px` : `bottom:${innerHeight - r.top + 6}px`); this.open = true } }"
     @keydown.escape.window="open = false" @scroll.window="open = false" @resize.window="open = false" class="relative inline-block text-left">
  <button type="button" x-ref="btn" @click="toggle()" :aria-expanded="open" aria-haspopup="menu" aria-label="{{ $label }}"
          class="flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white"
          :class="open && 'bg-gray-100 text-gray-800 dark:bg-white/5 dark:text-white'">
    <x-heroicon-o-ellipsis-horizontal class="h-5 w-5" />
  </button>
  <template x-teleport="body">
    <div x-show="open" x-cloak x-transition.opacity.duration.100ms @click.outside="open = false" @click="open = false" role="menu"
         class="fixed z-[100000] w-48 rounded-xl border border-gray-200 bg-white p-1.5 text-left shadow-lg dark:border-gray-800 dark:bg-gray-900" :style="style">
      {{ $slot }}
    </div>
  </template>
</div>
