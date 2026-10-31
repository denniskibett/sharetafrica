{{-- Data table (Alpine). Sits inside x-data="dataTable({ url })", which loads { data, meta, stats } as JSON.
     columns: [['key' => 'person', 'label' => 'Person', 'type' => 'person', 'sort' => 'name'], ...]
       types: person {name,email,initials,avatar,url} · pill {label,tone} · link {label,url} · text · muted · money · number · code
     Slots: filters (next to search), buttons (top right), actions (last cell; `row` is in scope), empty. --}}
@props(['title' => null, 'subtitle' => null, 'columns' => [], 'searchable' => true, 'selectable' => false, 'placeholder' => 'Search…'])
@php $span = count($columns) + ($selectable ? 1 : 0) + (isset($actions) ? 1 : 0); @endphp
<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-2xl border border-gray-200 bg-white pt-4 dark:border-gray-800 dark:bg-white/[0.03]']) }}>
  <div class="mb-4 flex flex-col gap-4 px-5 sm:px-6 xl:flex-row xl:items-center xl:justify-between">
    <div>
      @if ($title)<h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $title }}</h3>@endif
      @if ($subtitle)<p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ $subtitle }}</p>@endif
    </div>
    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
      @if ($searchable)
        <div class="relative">
          <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3.5 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" />
          <input type="search" x-model="search" placeholder="{{ $placeholder }}" aria-label="{{ $placeholder }}"
                 class="h-10 w-full rounded-lg border border-gray-300 bg-transparent pl-11 pr-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-violet-300 focus:outline-none focus:ring-4 focus:ring-violet-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 xl:w-[300px]">
        </div>
      @endif
      {{ $filters ?? '' }}
      {{ $buttons ?? '' }}
    </div>
  </div>

  <div class="custom-scrollbar max-w-full overflow-x-auto">
    <table class="min-w-full text-left">
      <thead class="border-y border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-gray-900">
        <tr>
          @if ($selectable)
            <th class="w-12 px-6 py-3"><input type="checkbox" :checked="allChecked" @change="toggleAll()" aria-label="Select all" class="h-4 w-4 rounded border-gray-300 text-gray-900 focus:ring-gray-500 dark:border-gray-700 dark:bg-gray-900"></th>
          @endif
          @foreach ($columns as $col)
            <th class="whitespace-nowrap px-6 py-3 text-xs font-medium text-gray-500 dark:text-gray-400 {{ $col['class'] ?? '' }}">
              @if (! empty($col['sort']))
                <button type="button" @click="sortBy('{{ $col['sort'] }}')" class="inline-flex items-center gap-1 hover:text-gray-800 dark:hover:text-white">{{ $col['label'] }}<span x-text="sortIcon('{{ $col['sort'] }}')"></span></button>
              @else
                {{ $col['label'] }}
              @endif
            </th>
          @endforeach
          @isset($actions)<th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400">Action</th>@endisset
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
        <template x-if="loading && !rows.length">
          <tr><td colspan="{{ $span }}" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">Loading…</td></tr>
        </template>
        <template x-if="error">
          <tr><td colspan="{{ $span }}" class="px-6 py-12 text-center text-sm text-red-600 dark:text-red-400"><span x-text="error"></span> <button type="button" @click="load()" class="ml-2 font-medium underline">Retry</button></td></tr>
        </template>
        <template x-if="!loading && !error && !rows.length">
          <tr><td colspan="{{ $span }}" class="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">{{ $empty ?? 'Nothing here yet.' }}<span x-show="search"> No match for "<span x-text="search"></span>".</span></td></tr>
        </template>
        <template x-for="row in rows" :key="row.id">
          <tr class="text-sm text-gray-700 transition hover:bg-gray-50/60 dark:text-gray-400 dark:hover:bg-white/[0.02]" :class="loading && 'opacity-60'">
            @if ($selectable)
              <td class="px-6 py-3"><input type="checkbox" :value="row.id" x-model="selected" aria-label="Select row" class="h-4 w-4 rounded border-gray-300 text-gray-900 focus:ring-gray-500 dark:border-gray-700 dark:bg-gray-900"></td>
            @endif
            @foreach ($columns as $col)
              @php $k = 'row.'.$col['key']; @endphp
              <td class="whitespace-nowrap px-6 py-3 {{ $col['class'] ?? '' }}">
                @switch($col['type'] ?? 'text')
                  @case('person')
                    <div class="flex items-center gap-3">
                      <span class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-violet-100 text-xs font-semibold text-violet-700 dark:bg-violet-500/15 dark:text-violet-300">
                        <template x-if="{{ $k }}?.avatar"><img :src="{{ $k }}.avatar" alt="" class="h-full w-full object-cover"></template>
                        <span x-show="!{{ $k }}?.avatar" x-text="{{ $k }}?.initials"></span>
                      </span>
                      <div class="min-w-0">
                        <a :href="{{ $k }}?.url || null" class="block font-medium text-gray-800 dark:text-white/90" :class="{{ $k }}?.url && 'hover:underline'" x-text="{{ $k }}?.name"></a>
                        <span class="block text-gray-500 dark:text-gray-400" x-text="{{ $k }}?.email"></span>
                      </div>
                    </div>
                    @break
                  @case('pill')
                    <span :class="sharet.pill({{ $k }}?.tone)" x-text="{{ $k }}?.label"></span>
                    @break
                  @case('link')
                    <a :href="{{ $k }}?.url" class="font-medium text-gray-800 hover:underline dark:text-white/90" x-text="{{ $k }}?.label"></a>
                    @break
                  @case('money')
                    <span class="font-medium text-gray-800 dark:text-white/90" x-text="sharet.money({{ $k }})"></span>
                    @break
                  @case('number')
                    <span x-text="sharet.number({{ $k }})"></span>
                    @break
                  @case('code')
                    <code class="rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs text-gray-700 dark:bg-white/5 dark:text-gray-300" x-text="{{ $k }}"></code>
                    @break
                  @case('muted')
                    <span class="text-gray-500 dark:text-gray-400" x-text="{{ $k }} ?? '—'"></span>
                    @break
                  @default
                    <span x-text="{{ $k }} ?? '—'"></span>
                @endswitch
              </td>
            @endforeach
            @isset($actions)<td class="px-6 py-3 text-right"><div class="flex items-center justify-end gap-1">{{ $actions }}</div></td>@endisset
          </tr>
        </template>
      </tbody>
    </table>
  </div>

  {{-- Footer: count, rows per page, pagination --}}
  <div class="flex flex-col gap-3 border-t border-gray-100 px-5 py-4 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between sm:px-6">
    <div class="flex items-center gap-3 text-sm text-gray-500 dark:text-gray-400">
      <span>Showing <span class="font-medium text-gray-800 dark:text-white/90" x-text="meta.from || 0"></span> to <span class="font-medium text-gray-800 dark:text-white/90" x-text="meta.to || 0"></span> of <span class="font-medium text-gray-800 dark:text-white/90" x-text="sharet.number(meta.total)"></span></span>
      <select x-model.number="perPage" aria-label="Rows per page" class="h-8 rounded-lg border border-gray-300 bg-transparent py-0 pl-2 pr-8 text-sm dark:border-gray-700 dark:bg-gray-900">
        <option value="10">10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option>
      </select>
    </div>
    <nav class="flex items-center gap-1" aria-label="Pagination">
      <button type="button" @click="go(meta.current_page - 1)" :disabled="meta.current_page <= 1"
              class="inline-flex h-9 items-center gap-1 rounded-lg border border-gray-300 px-3 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-40 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
        <x-heroicon-o-chevron-left class="h-4 w-4" /> Previous
      </button>
      <template x-for="(p, i) in pages" :key="i">
        <button type="button" @click="p !== '…' && go(p)" :disabled="p === '…'"
                class="hidden h-9 min-w-9 items-center justify-center rounded-lg px-2 text-sm font-medium sm:inline-flex"
                :class="p === meta.current_page ? 'bg-gray-900 text-white dark:bg-[#D4FF3D] dark:text-gray-900' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5'"
                x-text="p"></button>
      </template>
      <button type="button" @click="go(meta.current_page + 1)" :disabled="meta.current_page >= meta.last_page"
              class="inline-flex h-9 items-center gap-1 rounded-lg border border-gray-300 px-3 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-40 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/5">
        Next <x-heroicon-o-chevron-right class="h-4 w-4" />
      </button>
    </nav>
  </div>
</div>
