{{-- Public blog list (posts from Website › Blog). --}}
@extends('home.layouts.app')

@section('title', 'Sharet · Blog — Payments across East Africa, explained')
@section('description', 'Notes from the Sharet team on mobile money, cross-border payments and building one ledger across M-PESA, MTN, Airtel and banks in East Africa.')

@section('content')
  <section class="hero hero--compact">
    <div class="container container--wide">
      <span class="eyebrow"><span class="dot" aria-hidden="true"></span>Blog</span>
      <h1 class="hero-headline">Notes from <span class="lime">the ledger.</span></h1>
      <p class="hero-sub">How payments across East Africa actually work, and what we are building to make them cheaper.</p>
    </div>
  </section>

  <section class="snug">
    <div class="container container--wide">
      @if ($posts->isEmpty())
        <p class="lede">The first posts are on their way.</p>
      @else
        <div class="blog-grid">
          @foreach ($posts as $post)
            <a class="blog-card" href="{{ route('blog.show', $post->slug) }}">
              @if ($post->cover_url)<img loading="lazy" decoding="async" src="{{ $post->cover_url }}" alt="" />@endif
              <span class="label">{{ $post->published_at->format('j M Y') }} · {{ $post->reading_minutes }} min read</span>
              <h2>{{ $post->title }}</h2>
              <p>{{ $post->excerpt }}</p>
            </a>
          @endforeach
        </div>
        <div class="blog-pages">{{ $posts->links() }}</div>
      @endif
    </div>
  </section>
@endsection
