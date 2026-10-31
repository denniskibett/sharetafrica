{{-- resources/views/home/partials/card/cap-card.blade.php --}}
{{--
  Props:
  - $variant: 'std' | 'wide' | 'tall'  (default: 'std')
  - $num:     the small number/label in the corner (e.g., "01 / Merchants")
  - $title:   the card heading (plain text; use <br> for line breaks)
  - $body:    the paragraph (plain text)
  - $label:   optional small label at the bottom
  - $image:   optional image src (for 'tall' variant)
  - $href:    optional link URL — wraps body in a "read more" button if present
  - $cta:     optional CTA text (default: "Learn more")
--}}

@php
  $variant = $variant ?? 'std';
  $cta = $cta ?? 'Learn more';
  $cardClass = 'cap-card cap-card--' . $variant;
@endphp

<article class="{{ $cardClass }}">
  @if ($variant === 'tall' && !empty($image))
    <img src="{{ $image }}" alt="{{ $alt ?? '' }}" />
    <div class="cap-tall-meta">
      @isset($num)<span class="cap-num lime-text">{{ $num }}</span>@endisset
      <h3>{!! $title !!}</h3>
      <p>{{ $body }}</p>
      @isset($href)
        <a class="btn btn--ghost-on-tile btn--sm" href="{{ $href }}" style="margin-top: 12px;">{{ $cta }} →</a>
      @endisset
    </div>
  @else
    @isset($num)<span class="cap-num">{{ $num }}</span>@endisset
    <h3>{!! $title !!}</h3>
    <p>{{ $body }}</p>
    @isset($label)<span class="label">{{ $label }}</span>@endisset
    @isset($href)
      <a class="btn btn--ghost btn--sm" href="{{ $href }}" style="margin-top: auto;">{{ $cta }} →</a>
    @endisset
  @endif
</article>