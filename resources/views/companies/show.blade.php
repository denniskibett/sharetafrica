{{-- One business Sharet runs. Data: group_companies (Website › Companies). --}}
@extends('home.layouts.app')

@section('title', $company->name.' — A Sharet company')
@section('description', \Illuminate\Support\Str::limit($company->tagline.' '.$company->description, 155))

@section('content')
  <section class="hero hero--compact">
    <div class="container container--narrow">
      <a class="label" href="{{ route('company') }}#companies" style="display:block">← Our companies</a>
      <span class="eyebrow" style="margin-top: var(--space-5);"><span class="dot" aria-hidden="true"></span>{{ $company->sector }} · A Sharet company</span>
      <h1 class="article-title">{{ $company->name }}</h1>
      @if ($company->tagline)<p class="hero-sub">{{ $company->tagline }}</p>@endif
      @if ($company->url)
        <div class="hero-cta-row">
          <a class="btn btn--primary btn--lg" href="{{ $company->url }}" target="_blank" rel="noopener">Visit {{ $company->host }} ↗</a>
        </div>
      @endif
    </div>
  </section>

  <article class="snug">
    <div class="container container--narrow article-body">
      @if ($company->description)<p>{{ $company->description }}</p>@endif

      @if (! empty($company->services))
        <h2>What it does</h2>
        <ul class="company-services">
          @foreach ($company->services as $service)<li>{{ $service }}</li>@endforeach
        </ul>
      @endif

      <dl class="company-facts">
        @if ($company->served)<div><dt>Where</dt><dd>{{ $company->served }}</dd></div>@endif
        @if ($company->url)<div><dt>Website</dt><dd><a href="{{ $company->url }}" target="_blank" rel="noopener">{{ $company->host }}</a></dd></div>@endif
        @if ($company->email)<div><dt>Email</dt><dd><a href="mailto:{{ $company->email }}">{{ $company->email }}</a></dd></div>@endif
        @if ($company->phone)<div><dt>Phone</dt><dd><a href="tel:{{ preg_replace('/[^0-9+]/', '', $company->phone) }}">{{ $company->phone }}</a></dd></div>@endif
      </dl>

      @if ($others->isNotEmpty())
        <h2>More Sharet companies</h2>
        <div class="blog-grid">
          @foreach ($others as $o)
            <a class="blog-card" href="{{ route('companies.show', $o->slug) }}">
              <span class="label">{{ $o->sector }}</span>
              <h2 style="font-size:1.2rem">{{ $o->name }}</h2>
              <p>{{ $o->tagline }}</p>
            </a>
          @endforeach
        </div>
      @endif
    </div>
  </article>

  <script type="application/ld+json">{!! json_encode(['@context' => 'https://schema.org', '@type' => 'Organization', 'name' => $company->name, 'url' => $company->url,
    'description' => $company->description, 'email' => $company->email, 'telephone' => $company->phone,
    'parentOrganization' => ['@id' => rtrim(config('app.url'), '/').'/#org']], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endsection
