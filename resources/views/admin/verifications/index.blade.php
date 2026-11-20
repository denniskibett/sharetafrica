{{-- Administration › Verifications: people the automatic checks could not approve. Open one to see the photos, the checks and Approve / Reject. --}}
@extends('layouts.app')
@section('title', 'Verifications')

@section('content')
<div class="mx-auto max-w-[1100px] space-y-6">
  <div>
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Verifications</h1>
    <p class="mt-1 max-w-3xl text-sm text-gray-500 dark:text-gray-400">A person is verified when their ID can be read, the name on it matches the account, they are 18 or older and no other account uses the ID. Anyone who misses a check waits here. Open them, compare the selfie with the ID, then approve or reject with a reason they will see.</p>
  </div>

  <nav class="flex gap-2 text-sm" aria-label="Status">
    @foreach (['pending' => 'Waiting', 'rejected' => 'Rejected', 'approved' => 'Verified'] as $key => $label)
      <a href="{{ route('admin.verifications.index', ['tab' => $key]) }}" @class(['rounded-full px-4 py-2 font-medium',
          'bg-gray-900 text-white dark:bg-[#D4FF3D] dark:text-gray-900' => $tab === $key,
          'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-white/[0.06] dark:text-gray-300' => $tab !== $key])>
        {{ $label }} <span class="ml-1 opacity-70">{{ $counts[$key] ?? 0 }}</span>
      </a>
    @endforeach
  </nav>

  <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
    @forelse ($people as $p)
      <a href="{{ route('users.show', $p) }}#verification" class="flex items-center justify-between gap-4 border-b border-gray-100 px-5 py-4 last:border-0 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-white/[0.04]">
        <span class="min-w-0">
          <span class="block truncate font-medium text-gray-900 dark:text-white">{{ $p->name }}</span>
          <span class="block truncate text-xs text-gray-500">{{ $p->email }}{{ $p->phone ? ' · '.$p->phone : '' }}</span>
        </span>
        <span class="shrink-0 text-xs text-gray-500">{{ $p->updated_at?->timezone('Africa/Nairobi')->format('j M, H:i') }}</span>
      </a>
    @empty
      <p class="px-5 py-10 text-center text-sm text-gray-500">{{ $tab === 'pending' ? 'Nobody is waiting. Everyone who sent their photos passed the checks or has been reviewed.' : 'Nobody here.' }}</p>
    @endforelse
  </div>
  {{ $people->links() }}
</div>
@endsection
