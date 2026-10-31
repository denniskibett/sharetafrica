{{-- Website › Blog. Data: PostController@index as JSON. --}}
@extends('layouts.app')
@section('title', 'Blog')

@section('content')
<div x-data="dataTable({ url: '{{ route('admin.posts.index') }}', filters: { status: '' } })">
  <x-ui.page-header title="Blog" subtitle="Posts on sharet.africa/blog. Drafts stay private until you publish them.">
    <x-ui.button variant="secondary" icon="arrow-top-right-on-square" href="{{ route('blog.index') }}" target="_blank">View blog</x-ui.button>
    <x-ui.button icon="plus" href="{{ route('admin.posts.create') }}">New post</x-ui.button>
  </x-ui.page-header>

  <x-ui.stats :cols="3" class="mb-6">
    <x-ui.stat label="Posts" value="stats.total" hint="stats.total_hint" icon="newspaper" tone="sky" />
    <x-ui.stat label="Published" value="stats.published" hint="stats.published_hint" icon="globe-alt" tone="emerald" />
    <x-ui.stat label="Drafts" value="stats.drafts" hint="stats.drafts_hint" icon="pencil-square" tone="amber" />
  </x-ui.stats>

  <x-ui.table title="Posts" placeholder="Search posts…" :columns="[
      ['key' => 'title', 'label' => 'Title', 'type' => 'link', 'sort' => 'title'],
      ['key' => 'author', 'label' => 'Author', 'type' => 'muted'],
      ['key' => 'status', 'label' => 'Status', 'type' => 'pill'],
      ['key' => 'date', 'label' => 'Date', 'type' => 'muted', 'sort' => 'date'],
  ]">
    <x-slot:filters>
      <select x-model="filters.status" aria-label="Status" class="h-10 rounded-lg border border-gray-300 bg-transparent pl-3 pr-9 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
        <option value="">Any status</option><option value="published">Published</option><option value="draft">Draft</option>
      </select>
    </x-slot:filters>
    <x-slot:actions>
      <a x-show="row.url" :href="row.url" target="_blank" title="View" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-white/5"><x-heroicon-o-eye class="h-5 w-5" /></a>
      <a :href="row.title.url" title="Edit" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:hover:bg-white/5"><x-heroicon-o-pencil-square class="h-5 w-5" /></a>
      <button type="button" title="Delete" class="rounded-lg p-2 text-gray-500 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10"
              @click="sharet.confirm({ title: 'Delete “' + row.title.label + '”?', message: 'This cannot be undone.', tone: 'error', confirmText: 'Delete' })
                .then(ok => ok && sharet.send('DELETE', '{{ url('admin/blog') }}/' + row.id).then(() => load()).catch(() => {}))"><x-heroicon-o-trash class="h-5 w-5" /></button>
    </x-slot:actions>
    <x-slot:empty>No posts yet. Write the first one.</x-slot:empty>
  </x-ui.table>
</div>
@endsection
