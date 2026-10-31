{{-- The account menu, shared by the desktop header dropdown and the phone "Account" sheet: transactions, website, then the account. --}}
@php
    $user = auth()->user();
    $link = 'flex items-center gap-3 rounded-lg px-3 py-2.5 text-app-soft hover:bg-app-tile hover:text-app-ink';
@endphp
<div class="flex flex-col gap-0.5 text-sm">
  <a href="{{ route('pay.index') }}" class="{{ $link }}"><x-heroicon-o-arrows-right-left class="h-5 w-5" /> Transactions</a>
  <a href="{{ route('home') }}" class="{{ $link }}"><x-heroicon-o-globe-alt class="h-5 w-5" /> Sharet website</a>
  <div class="my-1 border-t border-app-line"></div>
  <a href="{{ route('profile.show') }}" class="{{ $link }}"><x-heroicon-o-user-circle class="h-5 w-5" /> Profile</a>
  <a href="{{ route('profile.edit') }}" class="{{ $link }}"><x-heroicon-o-cog-6-tooth class="h-5 w-5" /> Account settings</a>
  <a href="{{ route('profile.edit') }}#verification" class="{{ $link }}"><x-heroicon-o-identification class="h-5 w-5" /> Verification
    @unless (in_array($user->kyc_level, ['basic', 'standard'], true))<span class="ml-auto h-2 w-2 rounded-full bg-amber-500" aria-label="To do"></span>@endunless</a>
  <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-app-line pt-1">
    @csrf
    <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-app-danger hover:bg-app-tile"><x-heroicon-o-arrow-left-start-on-rectangle class="h-5 w-5" /> Sign out</button>
  </form>
</div>
