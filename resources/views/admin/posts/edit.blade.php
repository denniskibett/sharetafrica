{{-- Write / edit a blog post. Saves in the background (multipart, so the cover image uploads too). --}}
@extends('layouts.app')
@section('title', $post->exists ? 'Edit post' : 'New post')

@section('content')
<form x-data="{ status: '{{ $post->status }}', errors: {}, busy: false, cover: '{{ $post->cover_url }}' }"
      @submit.prevent="busy = true; errors = {};
        sharet.send('POST', '{{ $post->exists ? route('admin.posts.update', $post) : route('admin.posts.store') }}', new FormData($el))
          .then(r => { if (r.edit) location.href = r.edit })
          .catch(e => errors = Object.fromEntries(Object.entries(e.errors || {}).map(([k, v]) => [k, v[0]])))
          .finally(() => busy = false)">
  <x-ui.page-header :title="$post->exists ? 'Edit post' : 'New post'" subtitle="Write it like you would explain it to a customer. Headings and short paragraphs read best on phones.">
    <x-ui.button variant="secondary" icon="arrow-left" href="{{ route('admin.posts.index') }}">All posts</x-ui.button>
    @if ($post->exists && $post->status === 'published')
      <x-ui.button variant="secondary" icon="eye" href="{{ route('blog.show', $post->slug) }}" target="_blank">View</x-ui.button>
    @endif
    <x-ui.button type="submit" icon="check" x-bind:disabled="busy" x-text="status === 'published' ? 'Save & publish' : 'Save draft'">Save</x-ui.button>
  </x-ui.page-header>

  <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
    <div class="space-y-5">
      <div>
        <input name="title" value="{{ $post->title }}" required placeholder="Post title" aria-label="Title"
               class="w-full rounded-xl border border-gray-300 bg-white px-5 py-4 text-2xl font-semibold text-gray-900 placeholder:text-gray-300 focus:border-violet-300 focus:outline-none dark:border-gray-700 dark:bg-white/[0.03] dark:text-white">
        <p x-show="errors.title" x-text="errors.title" class="mt-1 text-xs text-red-600"></p>
      </div>
      <x-ui.rich-text name="body" :value="$post->body" />
    </div>

    <div class="space-y-6">
      <x-ui.card title="Publishing">
        <div class="space-y-4 text-sm">
          <label class="flex items-center gap-3"><input type="radio" name="status" value="draft" x-model="status" class="text-gray-900"> Draft (only you can see it)</label>
          <label class="flex items-center gap-3"><input type="radio" name="status" value="published" x-model="status" class="text-gray-900"> Published</label>
          <div x-show="status === 'published'">
            <label for="published_at" class="mb-1.5 block font-medium text-gray-700 dark:text-gray-300">Publish date</label>
            <input id="published_at" type="datetime-local" name="published_at" value="{{ $post->published_at?->format('Y-m-d\TH:i') }}" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 dark:border-gray-700 dark:bg-gray-900">
            <p class="mt-1 text-xs text-gray-500">Leave empty to publish now. A future date schedules it.</p>
          </div>
        </div>
      </x-ui.card>

      <x-ui.card title="Cover image">
        <img x-show="cover" :src="cover" alt="" class="mb-4 aspect-[16/9] w-full rounded-lg object-cover">
        <input type="file" name="cover" accept="image/jpeg,image/png,image/webp" @change="cover = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : cover" class="text-sm">
        <p class="mt-1 text-xs text-gray-500">1600×900 works best. Also used when the post is shared.</p>
        <p x-show="errors.cover" x-text="errors.cover" class="mt-1 text-xs text-red-600"></p>
      </x-ui.card>

      <x-ui.card title="Search & sharing">
        <div class="space-y-4">
          <div>
            <label for="slug" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Web address</label>
            <div class="flex items-center rounded-lg border border-gray-300 dark:border-gray-700"><span class="pl-3 text-sm text-gray-400">/blog/</span><input id="slug" name="slug" value="{{ $post->slug }}" placeholder="from-the-title" class="h-11 w-full border-0 bg-transparent px-1 text-sm focus:ring-0"></div>
            <p x-show="errors.slug" x-text="errors.slug" class="mt-1 text-xs text-red-600"></p>
          </div>
          <div>
            <label for="excerpt" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Summary</label>
            <textarea id="excerpt" name="excerpt" rows="3" maxlength="300" placeholder="One or two sentences for the blog list and search results." class="w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900">{{ $post->excerpt }}</textarea>
          </div>
        </div>
      </x-ui.card>
    </div>
  </div>
</form>
@endsection
