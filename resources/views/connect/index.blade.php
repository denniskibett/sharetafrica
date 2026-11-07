{{-- Account › Connected apps: platforms the person allowed to act for their Sharet account; disconnect any time. --}}
@extends('layouts.app')
@section('title', 'Connected apps')

@section('content')
<div class="mx-auto max-w-[760px]">
  <h1 class="text-2xl font-semibold">Connected apps</h1>
  <p class="mt-1 text-sm text-app-muted">Businesses you allowed to collect or send money for your Sharet account. Disconnecting stops them straight away.</p>

  @if (session('status'))<div class="mt-4 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-800" role="status">{{ session('status') }}</div>@endif

  <div class="mt-6 overflow-hidden rounded-2xl bg-app-panel">
    @forelse ($rows as $c)
      <div class="flex flex-col gap-3 border-b border-app-ink/10 px-5 py-4 last:border-0 sm:flex-row sm:items-center">
        <div class="min-w-0 flex-1">
          <p class="font-semibold">{{ $name($c) }}</p>
          <p class="text-sm text-app-muted">May {{ collect($c->scopes)->map(fn ($s) => $s === 'collect' ? 'collect for you' : 'send from your wallet')->join(' and ') }} · since {{ $c->approved_at?->timezone('Africa/Nairobi')->format('j M Y') }}</p>
        </div>
        <form method="POST" action="{{ route('connections.revoke', $c) }}">@csrf
          <button class="h-10 rounded-full bg-app-tile px-4 text-sm font-semibold hover:bg-app-raised">Disconnect</button>
        </form>
      </div>
    @empty
      <div class="flex flex-col items-center gap-2 px-5 py-14 text-center">
        <x-heroicon-o-link class="h-8 w-8 text-app-faint" />
        <p class="font-semibold">No connected apps</p>
        <p class="max-w-sm text-sm text-app-muted">When a business like scharge asks to collect for you, it will show here.</p>
      </div>
    @endforelse
  </div>
</div>
@endsection
