{{-- Account links shared by the desktop account menu and the phone "More" sheet. --}}
@php $user = auth()->user(); @endphp
<div class="flex flex-col gap-0.5 text-sm">
  <a href="{{ route('profile.show') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-[#C9C9C2] hover:bg-white/[0.05] hover:text-white"><x-heroicon-o-user-circle class="h-5 w-5" /> Profile</a>
  <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-[#C9C9C2] hover:bg-white/[0.05] hover:text-white"><x-heroicon-o-cog-6-tooth class="h-5 w-5" /> Account settings</a>
  <a href="{{ route('profile.edit') }}#verification" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-[#C9C9C2] hover:bg-white/[0.05] hover:text-white"><x-heroicon-o-identification class="h-5 w-5" /> Verification</a>
  @if (in_array($user->intent, ['merchant', 'business'], true) || $user->company_id)
    <a href="{{ route('settings.company') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-[#C9C9C2] hover:bg-white/[0.05] hover:text-white"><x-heroicon-o-building-storefront class="h-5 w-5" /> Business settings</a>
  @endif
  <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-white/[0.08] pt-1">
    @csrf
    <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-[#FF7A7A] hover:bg-white/[0.05]"><x-heroicon-o-arrow-left-start-on-rectangle class="h-5 w-5" /> Sign out</button>
  </form>
</div>
