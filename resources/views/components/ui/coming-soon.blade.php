{{-- A designed feature that has no backend yet, shown honestly: what it will do, when, and a look at the designed
     screen (an inert picture: nothing in it can be tapped or sent). "Tell me when it's ready" stays disabled until
     there is a sign-up for feature alerts (the waiting list is for accounts, not features).
     Props: title, lede, icon (heroicon), tone (tile classes), stage (pill text), features [[icon, title, text]].
     Slots: default = the preview screen; `today` (optional) = what already works. --}}
@props(['title', 'lede', 'icon', 'tone', 'stage' => 'Coming soon', 'features' => []])
<div class="mx-auto grid max-w-5xl items-start gap-6 lg:grid-cols-[minmax(0,1fr)_380px] lg:gap-10 lg:py-4">
  <div class="flex flex-col gap-6">
    <div>
      <a href="{{ route('dashboard') }}" class="mb-4 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-app-tile text-app-ink hover:bg-app-raised" aria-label="Back to home"><x-heroicon-o-chevron-left class="h-5 w-5" /></a>
      <div class="flex items-center gap-3">
        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $tone }}"><x-dynamic-component :component="'heroicon-o-'.$icon" class="h-6 w-6" /></span>
        <span class="rounded-full bg-app-wait/15 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-app-wait">{{ $stage }}</span>
      </div>
      <h1 class="mt-4">{{ $title }}</h1>
      <p class="mt-2 max-w-xl text-[15px] text-app-muted">{{ $lede }}</p>
    </div>

    @isset($today)
      <div class="rounded-[24px] bg-app-panel p-5">{{ $today }}</div>
    @endisset

    <section class="rounded-[24px] bg-app-panel px-5 py-2">
      <h2 class="pt-3 text-base font-semibold">What it will do</h2>
      <ul>
        @foreach ($features as [$fIcon, $fTitle, $fText])
          <li class="flex items-center gap-3 border-b border-app-line py-3 last:border-0">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-app-tile text-app-ink"><x-dynamic-component :component="'heroicon-o-'.$fIcon" class="h-5 w-5" /></span>
            <span class="min-w-0"><span class="block text-sm font-semibold">{{ $fTitle }}</span><span class="block text-xs text-app-muted">{{ $fText }}</span></span>
          </li>
        @endforeach
      </ul>
    </section>

    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
      <button type="button" disabled aria-describedby="soon-note" class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-gray-900 px-6 text-[15px] font-semibold text-white disabled:cursor-not-allowed disabled:opacity-40">
        <x-heroicon-o-bell class="h-5 w-5" /> Tell me when it's ready
      </button>
      <p id="soon-note" class="text-xs text-app-muted sm:max-w-[240px]">Alerts for new features are not switched on yet. Nothing here moves money.</p>
    </div>
  </div>

  {{-- The designed screen, as a picture of the phone --}}
  <figure class="mx-auto w-full max-w-[360px]">
    <div class="pointer-events-none relative select-none overflow-hidden rounded-[36px] bg-app-panel p-5 pt-6 shadow-[0_12px_32px_rgb(17_17_20/0.10)] ring-1 ring-app-line" inert aria-hidden="true">
      <div class="flex flex-col gap-3.5 opacity-90">{{ $slot }}</div>
    </div>
    <figcaption class="mt-3 flex items-center justify-center gap-1.5 text-xs text-app-muted"><x-heroicon-o-eye class="h-4 w-4" /> Preview of the design, with sample details</figcaption>
  </figure>
</div>
