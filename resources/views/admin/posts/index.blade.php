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
      ['key' => 'views', 'label' => 'Views', 'type' => 'number'],
      ['key' => 'readers', 'label' => 'Readers', 'type' => 'number'],
      ['key' => 'read_through', 'label' => 'Read to the end', 'type' => 'muted'],
      ['key' => 'date', 'label' => 'Date', 'type' => 'muted', 'sort' => 'date'],
  ]">
    <x-slot:filters>
      <select x-model="filters.status" aria-label="Status" class="h-10 rounded-lg border border-gray-300 bg-transparent pl-3 pr-9 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
        <option value="">Any status</option><option value="published">Published</option><option value="draft">Draft</option>
      </select>
    </x-slot:filters>
    <x-slot:actions>
      <x-ui.row-menu>
        <x-ui.row-menu-item icon="pencil-square" x-bind:href="row.title.url">Edit</x-ui.row-menu-item>
        <x-ui.row-menu-item icon="arrow-top-right-on-square" x-show="row.url" x-bind:href="row.url" target="_blank">View on blog</x-ui.row-menu-item>
        <x-ui.row-menu-item icon="trash" danger
            @click="sharet.confirm({ title: 'Delete this post?', message: 'This cannot be undone.', tone: 'error', confirmText: 'Delete', details: [['Title', row.title.label], ['Status', row.status.label], ['Date', row.date]] })
              .then(ok => ok && sharet.send('DELETE', '{{ url('admin/blog') }}/' + row.id).then(() => load()).catch(() => {}))">Delete</x-ui.row-menu-item>
      </x-ui.row-menu>
    </x-slot:actions>
    <x-slot:empty>No posts yet. Write the first one.</x-slot:empty>
  </x-ui.table>
</div>
@endsection
