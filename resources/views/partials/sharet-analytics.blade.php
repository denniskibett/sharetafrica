{{-- Cross-site analytics (first-party tracker, data-site "sharet"): the script in production when SHARET_ANALYTICS_URL is set,
     plus any conversion waiting to be counted. A conversion set with session()->put('sa_conversion', '<name>') is pulled here,
     so it fires once on the first page that renders after the action, even across redirects. No personal data in names. --}}
@php
    $saUrl = rtrim((string) config('services.sharet_analytics.url'), '/');
    $saConversions = array_values(array_unique(array_filter([session()->pull('sa_conversion'), $saConversion ?? null])));
@endphp
@if ($saUrl && app()->environment('production'))
<script defer src="{{ $saUrl }}/js/sharet-analytics.js" data-site="{{ config('services.sharet_analytics.site', 'sharet') }}"></script>
@endif
@foreach ($saConversions as $saName)
<script>(window.sa=window.sa||[]).push(['conversion',@json($saName)])</script>
@endforeach
