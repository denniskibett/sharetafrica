{{-- Search landing pages (/mpesa-api, /payment-gateway-kenya, /bulk-payments-kenya). Each page sets $lp and its title and
     description; everything here is a fact from /docs and the site (keep in step with docs/index.blade.php). The FAQ shows on
     the page and as FAQPage JSON-LD, so the two never differ. --}}
@extends('site.layout')
@php
    $seoNodes = [
        \App\Support\Seo::breadcrumbs([$lp['crumb'] => url()->current()]),
        \App\Support\Seo::faq($lp['faq']),
        \App\Support\Seo::webApi(),
    ];
    $related = collect([
        'landing.mpesa-api' => 'M-PESA API and STK push',
        'landing.gateway' => 'Payment gateway for Kenya',
        'landing.bulk' => 'Bulk payments and B2C payouts',
        'docs' => 'Sharet API docs',
        'developers' => 'Developers',
    ])->reject(fn ($label, $route) => request()->routeIs($route));
@endphp

@section('content')
<div class="ui web pg lp">
@include('site.partials.header')

<div class="wr phero"><div class="split" style="align-items:center"><div>
  <span class="lbl">{{ $lp['label'] }}</span>
  <h1 class="mega pg">{{ $lp['h1'] }}</h1>
  <p class="lead">{{ $lp['lead'] }}</p>
  <div class="row2" style="margin-top:28px;flex-wrap:wrap;gap:12px">
    <a class="wb" href="{{ route('developers') }}#apply" data-sa-click="{{ $lp['key'] }}_sandbox">Request sandbox access</a>
    <a class="wb o" href="{{ route('docs') }}{{ $lp['docs'] }}" data-sa-click="{{ $lp['key'] }}_docs">Read the API docs</a>
  </div>
</div><div><div class="code">{!! $lp['code'] !!}</div></div></div></div>

<div class="wr"><div class="stat">@foreach ($lp['stats'] as $b => $s)<div><b>{{ $b }}</b><span>{{ $s }}</span></div>@endforeach</div></div>

<div class="wr sect"><span class="lbl">HOW IT WORKS</span><h2 class="h2w" style="margin-top:12px">{{ $lp['steps_title'] }}</h2>
  <ol class="lp-steps">@foreach ($lp['steps'] as $step)<li><span>{!! $step !!}</span></li>@endforeach</ol>
</div>

<div class="wr sect" style="padding-top:0"><span class="lbl">WHAT YOU GET</span><h2 class="h2w" style="margin-top:12px">{{ $lp['features_title'] }}</h2>
  <div class="lp-grid">@foreach ($lp['features'] as $h => $p)<div class="lp-card"><h3>{{ $h }}</h3><p>{!! $p !!}</p></div>@endforeach</div>
</div>

<div class="wr sect" id="faq" style="padding-top:0"><span class="lbl">QUESTIONS</span><h2 class="h2w" style="margin-top:12px">{{ $lp['faq_title'] }}</h2>
  <div class="faq">@foreach ($lp['faq'] as $q => $a)<details @if ($loop->first) open @endif><summary>{{ $q }}</summary><p>{{ $a }}</p></details>@endforeach</div>
</div>

<div class="wr sect" style="padding-top:0"><span class="lbl">KEEP READING</span>
  <nav class="lp-links" aria-label="Related pages">@foreach ($related as $route => $label)<a href="{{ route($route) }}">{{ $label }}</a>@endforeach</nav>
</div>

<div class="wr sect tail"><div class="panelw brandband" style="min-height:auto;align-items:center;text-align:center;padding-block:72px">
  <h2 class="h2w">{{ $lp['cta'] }}</h2>
  <p class="lead" style="color:#C9C9C2;margin-inline:auto;text-align:center">Tell us what you're building and we'll send sandbox keys. Live keys follow once your business is verified.</p>
  <div class="row2" style="justify-content:center;flex-wrap:wrap;gap:12px"><a class="wb l" href="{{ route('developers') }}#apply" data-sa-click="{{ $lp['key'] }}_cta">Request sandbox access</a></div>
</div></div>

@include('site.partials.footer')
</div>
@endsection
