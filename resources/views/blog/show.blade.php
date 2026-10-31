{{-- One blog post. Body is HTML cleaned on save (App\Support\RichText). --}}
@extends('home.layouts.app')

@section('title', $post->title.' · Sharet blog')
@section('description', $post->excerpt)

@section('content')
  <article class="snug">
    <div class="container container--narrow">
      <a class="label" href="{{ route('blog.index') }}">← All posts</a>
      <h1 class="article-title">{{ $post->title }}</h1>
      <p class="label">{{ $post->author?->name ? $post->author->name.' · ' : '' }}{{ $post->published_at->format('j F Y') }} · {{ $post->reading_minutes }} min read</p>
      @if ($post->cover_url)<img class="article-cover" src="{{ $post->cover_url }}" alt="" />@endif
      <div class="article-body">{!! $post->body !!}</div>
    </div>
  </article>

  <script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@type' => 'BlogPosting', 'headline' => $post->title, 'description' => $post->excerpt,
    'datePublished' => $post->published_at->toAtomString(), 'dateModified' => $post->updated_at->toAtomString(), 'image' => $post->cover_url,
    'author' => ['@type' => 'Person', 'name' => $post->author?->name ?? 'Sharet Africa'], 'publisher' => ['@id' => rtrim(config('app.url'), '/').'/#org'],
    'mainEntityOfPage' => url()->current()], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endsection
