{{-- Administration › Businesses: businesses registered in Business settings (KYB). Approve to let their live API keys take payments; reject with a reason the owner sees. --}}
@extends('layouts.app')
@section('title', 'Businesses')

@section('content')
<div class="mx-auto max-w-[1100px] space-y-6">
  <div>
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Businesses</h1>
    <p class="mt-1 max-w-3xl text-sm text-gray-500 dark:text-gray-400">Anyone with a Sharet account can register a business in Business settings. Check the registration, KRA PIN, directors and settlement account, then approve. Live API keys only take payments for verified businesses.</p>
  </div>

  <nav class="flex gap-2 text-sm" aria-label="Status">
    @foreach ($tabs as $key => $label)
      <a href="{{ route('admin.businesses.index', ['tab' => $key]) }}" @class(['rounded-full px-4 py-2 font-medium',
          'bg-gray-900 text-white dark:bg-[#D4FF3D] dark:text-gray-900' => $tab === $key,
          'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-white/[0.06] dark:text-gray-300' => $tab !== $key])>
        {{ $label }} <span class="ml-1 opacity-70">{{ $counts[$key] ?? 0 }}</span>
      </a>
    @endforeach
  </nav>

  <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
    @forelse ($businesses as $b)
      @php $review = $b->settings['kyb_review'] ?? null; $own = $b->users->contains('id', $me->id); @endphp
      <div class="border-b border-gray-100 px-5 py-4 last:border-0 dark:border-gray-800"
           x-data="{ open: false, reason: '', busy: false, err: '',
             async decide(decision) { this.busy = true; this.err = '';
               try { await window.sharet.send('POST', '{{ route('admin.businesses.decide', $b) }}', { decision, reason: this.reason }); window.location.reload(); }
               catch (e) { this.err = Object.values(e.errors || {})[0]?.[0] || e.body?.message || 'That did not work.'; }
               finally { this.busy = false; } } }">
        <div class="flex flex-wrap items-start justify-between gap-4">
          <div class="min-w-0">
            <p class="truncate font-medium text-gray-900 dark:text-white">{{ $b->name }} <span class="ml-1 text-xs font-normal text-gray-500">{{ $b->sharet_id }}</span></p>
            <p class="truncate text-xs text-gray-500">
              {{ $b->legal_name ?: 'No legal name' }} · {{ $b->registration_number ?: 'no registration no.' }} · {{ $b->tax_id ?: 'no KRA PIN' }}
            </p>
            <p class="truncate text-xs text-gray-500">Owner: {{ $b->users->map(fn ($u) => $u->name.' ('.$u->email.')')->join(', ') ?: 'nobody' }}</p>
            <p class="text-xs text-gray-500">Profile {{ $b->kyb_progress['done'] }}/{{ $b->kyb_progress['of'] }} complete · updated {{ $b->updated_at?->timezone('Africa/Nairobi')->format('j M, H:i') }}
              @if ($b->verified_at) · verified {{ $b->verified_at->timezone('Africa/Nairobi')->format('j M Y') }}{{ $b->verifier ? ' by '.$b->verifier->name : '' }}@endif
              @if (($review['status'] ?? null) === 'rejected') · rejected: “{{ $review['reason'] }}”@endif
            </p>
          </div>
          @unless ($own)
            <div class="flex shrink-0 gap-2">
              @unless ($b->verified_at)<x-ui.button icon="check" x-bind:disabled="busy" @click="decide('approve')">Approve</x-ui.button>@endunless
              <x-ui.button variant="secondary" icon="x-mark" x-bind:disabled="busy" @click="open = !open">{{ $b->verified_at ? 'Revoke' : 'Reject' }}</x-ui.button>
            </div>
          @else
            <span class="text-xs text-gray-500">Your own business: another staff member reviews it.</span>
          @endunless
        </div>
        <div x-show="open" x-cloak class="mt-3 space-y-2">
          <x-ui.field label="Reason (the owner sees it)" name="reason" model="reason" placeholder="The KRA PIN does not match the registered name." />
          <x-ui.button variant="secondary" x-bind:disabled="busy" @click="decide('reject')">Send back</x-ui.button>
        </div>
        <p x-show="err" x-text="err" class="mt-2 text-sm text-red-600"></p>
      </div>
    @empty
      <p class="px-5 py-10 text-center text-sm text-gray-500">{{ $tab === 'pending' ? 'No businesses are waiting.' : 'Nobody here.' }}</p>
    @endforelse
  </div>
  {{ $businesses->links() }}
</div>
@endsection
