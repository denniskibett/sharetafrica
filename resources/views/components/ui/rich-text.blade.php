{{-- Rich-text editor (no library): toolbar for headings, bold/italic, lists, quotes, links, code.
     Writes HTML into a hidden input named {name}; the server cleans it (App\Support\RichText). --}}
@props(['name' => 'body', 'value' => ''])
<div x-data="{
        html: '',
        cmd(c, v = null) { document.execCommand(c, false, v); this.$refs.ed.focus(); this.sync() },
        block(tag) { this.cmd('formatBlock', tag) },
        link() { const url = prompt('Link to (https://…)'); if (url) this.cmd('createLink', url) },
        sync() { this.html = this.$refs.ed.innerHTML },
     }"
     x-init="$refs.ed.innerHTML = $refs.initial.innerHTML; sync()"
     class="overflow-hidden rounded-xl border border-gray-300 dark:border-gray-700">
  <template x-ref="initial">{!! $value !!}</template>
  <div class="flex flex-wrap gap-1 border-b border-gray-200 bg-gray-50 p-2 dark:border-gray-800 dark:bg-gray-900" role="toolbar" aria-label="Formatting">
    @foreach ([['block', "'h2'", 'H2', 'Heading'], ['block', "'h3'", 'H3', 'Subheading'], ['block', "'p'", '¶', 'Paragraph'], ['cmd', "'bold'", 'B', 'Bold'], ['cmd', "'italic'", 'I', 'Italic'], ['cmd', "'underline'", 'U', 'Underline'], ['cmd', "'insertUnorderedList'", '• List', 'Bulleted list'], ['cmd', "'insertOrderedList'", '1. List', 'Numbered list'], ['block', "'blockquote'", '❝', 'Quote'], ['block', "'pre'", '</>', 'Code block'], ['link', '', 'Link', 'Add link'], ['cmd', "'unlink'", 'Unlink', 'Remove link'], ['cmd', "'removeFormat'", 'Clear', 'Clear formatting']] as [$fn, $arg, $text, $title])
      <button type="button" title="{{ $title }}" @mousedown.prevent @click="{{ $fn }}({{ $arg }})"
              class="h-8 min-w-8 rounded-md px-2 text-sm font-semibold text-gray-700 hover:bg-white hover:shadow-theme-xs dark:text-gray-300 dark:hover:bg-white/10">{{ $text }}</button>
    @endforeach
  </div>
  <div x-ref="ed" contenteditable="true" @input="sync()" @blur="sync()" aria-label="Post body"
       class="prose-sharet min-h-[420px] bg-white px-5 py-4 text-gray-800 focus:outline-none dark:bg-transparent dark:text-gray-200"></div>
  <input type="hidden" name="{{ $name }}" :value="html">
</div>
