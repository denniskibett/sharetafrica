{{-- The account menu, shared by the desktop header dropdown and the phone "Account" sheet: transactions, website, the
     designed features that are coming (read-only previews), then the account and Help. --}}
@php
    $user = auth()->user();
    $link = 'flex items-center gap-3 rounded-lg px-3 py-2.5 text-app-soft hover:bg-app-tile hover:text-app-ink';
@endphp
<div class="flex flex-col gap-0.5 text-sm">
  <a href="{{ route('pay.index') }}" class="{{ $link }}"><x-heroicon-o-arrows-right-left class="h-5 w-5" /> Transactions</a>
  <a href="{{ route('home') }}" class="{{ $link }}"><x-heroicon-o-globe-alt class="h-5 w-5" /> Sharet website</a>
  <div class="my-1 border-t border-app-line"></div>
  @foreach ([['requests', 'arrow-down-left', 'Request and split'], ['card', 'credit-card', 'Card'], ['abroad', 'globe-europe-africa', 'Send abroad']] as [$soonRoute, $soonIcon, $soonLabel])
    <a href="{{ route($soonRoute) }}" class="{{ $link }}"><x-dynamic-component :component="'heroicon-o-'.$soonIcon" class="h-5 w-5" /> {{ $soonLabel }}
      <span class="ml-auto rounded-full bg-app-wait/15 px-2 py-0.5 text-[10.5px] font-semibold text-app-wait">Soon</span></a>
  @endforeach
  <div class="my-1 border-t border-app-line"></div>
  <a href="{{ route('profile.show') }}" class="{{ $link }}"><x-heroicon-o-user-circle class="h-5 w-5" /> Profile</a>
  <a href="{{ route('profile.edit') }}" class="{{ $link }}"><x-heroicon-o-cog-6-tooth class="h-5 w-5" /> Account settings</a>
  <a href="{{ route('profile.edit') }}#verification" class="{{ $link }}"><x-heroicon-o-identification class="h-5 w-5" /> Verification
    @unless (in_array($user->kyc_level, ['basic', 'standard'], true))<span class="ml-auto h-2 w-2 rounded-full bg-amber-500" aria-label="To do"></span>@endunless</a>
  <a href="{{ route('help') }}" class="{{ $link }}"><x-heroicon-o-lifebuoy class="h-5 w-5" /> Help</a>
  <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-app-line pt-1">
    @csrf
    <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-app-danger hover:bg-app-tile"><x-heroicon-o-arrow-left-start-on-rectangle class="h-5 w-5" /> Sign out</button>
  </form>
</div>
