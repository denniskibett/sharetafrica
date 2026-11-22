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
    @foreach (['pending' => 'Waiting', 'rejected' => 'Rejected', 'approved' => 'Verified', 'unfreeze' => 'Unfreeze'] as $key => $label)
      <a href="{{ route('admin.verifications.index', ['tab' => $key]) }}" @class(['rounded-full px-4 py-2 font-medium',
          'bg-gray-900 text-white dark:bg-[#D4FF3D] dark:text-gray-900' => $tab === $key,
          'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-white/[0.06] dark:text-gray-300' => $tab !== $key])>
        {{ $label }} <span class="ml-1 opacity-70">{{ $counts[$key] ?? 0 }}</span>
      </a>
    @endforeach
  </nav>

  @if (session('status'))
    <p class="rounded-xl bg-gray-100 px-4 py-3 text-sm text-gray-800 dark:bg-white/[0.06] dark:text-gray-200">{{ session('status') }}</p>
  @endif

  @if ($tab === 'unfreeze')
  <p class="max-w-3xl text-sm text-gray-500 dark:text-gray-400">Frozen accounts asking to unfreeze. The PIN was right; the selfie is the second check, because a thief may have the phone and the PIN. Compare the new selfie with the verification selfie. Approve only when it is clearly the same person; otherwise reject with a reason they will see. The account stays frozen until you decide.</p>
  <div class="space-y-4">
    @forelse ($people as $p)
      <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-wrap items-baseline justify-between gap-2">
          <a href="{{ route('users.show', $p) }}#verification" class="font-medium text-gray-900 hover:underline dark:text-white">{{ $p->name }}</a>
          <span class="text-xs text-gray-500">{{ $p->sharet_id }}{{ $p->phone ? ' · '.$p->phone : '' }} · frozen {{ $p->frozen_at?->timezone('Africa/Nairobi')->format('j M, H:i') }} · asked {{ $p->unfreeze_requested_at?->timezone('Africa/Nairobi')->format('j M, H:i') }}</span>
        </div>
        @php($docs = \App\Support\Kyc::data($p)['documents'] ?? [])
        <div class="mt-4 grid grid-cols-2 gap-4 sm:max-w-md">
          @foreach (['selfie_center' => 'Verification selfie', 'unfreeze_selfie' => 'New selfie'] as $type => $label)
            <figure>
              @if (filled($docs[$type]['path'] ?? null))
                <img src="{{ route('profile.document', ['user' => $p->id, 'type' => $type, 'v' => crc32($docs[$type]['path'])]) }}" alt="{{ $label }} of {{ $p->name }}" class="aspect-[3/4] w-full rounded-xl object-cover">
              @else
                <div class="flex aspect-[3/4] w-full items-center justify-center rounded-xl bg-gray-100 text-center text-xs text-gray-500 dark:bg-white/[0.06]">No {{ strtolower($label) }}</div>
              @endif
              <figcaption class="mt-1 text-xs text-gray-500">{{ $label }}</figcaption>
            </figure>
          @endforeach
        </div>
        <div class="mt-4 flex flex-wrap items-start gap-3">
          <form method="POST" action="{{ route('admin.verifications.unfreeze', $p) }}">
            @csrf
            <input type="hidden" name="decision" value="approve">
            <button class="rounded-full bg-gray-900 px-4 py-2 text-sm font-medium text-white dark:bg-[#D4FF3D] dark:text-gray-900">Same person: unfreeze</button>
          </form>
          <form method="POST" action="{{ route('admin.verifications.unfreeze', $p) }}" class="flex flex-wrap gap-2">
            @csrf
            <input type="hidden" name="decision" value="reject">
            <input name="reason" required maxlength="300" placeholder="Reason they will see" class="rounded-full border border-gray-300 px-4 py-2 text-sm dark:border-gray-700 dark:bg-transparent">
            <button class="rounded-full bg-gray-100 px-4 py-2 text-sm font-medium text-gray-800 dark:bg-white/[0.06] dark:text-gray-200">Keep frozen</button>
          </form>
        </div>
      </div>
    @empty
      <p class="rounded-2xl border border-gray-200 bg-white px-5 py-10 text-center text-sm text-gray-500 dark:border-gray-800 dark:bg-white/[0.03]">Nobody is asking to unfreeze.</p>
    @endforelse
  </div>
  @else
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
  @endif
  {{ $people->links() }}
</div>
@endsection
