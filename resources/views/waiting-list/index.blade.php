{{-- Admin: people waiting (users.onboarding_status = waiting_list). Invite emails them a link to onboarding. --}}
@extends('layouts.app')
@section('title', 'Waiting list')

@section('content')
@php
    $intents = ['individual' => 'Personal', 'merchant' => 'Merchant', 'business' => 'Business / trade', 'techie' => 'Developer'];
    $rows = $people->map(fn ($p) => [
        'id' => $p->id, 'name' => $p->name, 'email' => $p->email, 'phone' => $p->phone,
        'intent' => $intents[$p->intent] ?? 'Not chosen',
        'source' => str_replace('-', ' ', $p->waitingListEntry?->source ?? 'sign-up'),
        'joined' => $p->created_at?->timezone('Africa/Nairobi')->format('j M Y'),
    ]);
@endphp

<div x-data="{ people: @js($rows), busy: null }">
  <div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Waiting list</h1>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">People waiting for access. Inviting someone emails them a link to choose their lane and finish setting up.</p>
  </div>

  <p x-show="!people.length" class="rounded-2xl border border-dashed border-gray-300 p-10 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">Nobody is waiting right now.</p>

  <div x-show="people.length" class="overflow-x-auto rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
    <table class="min-w-full text-left text-sm">
      <thead><tr class="border-b border-gray-100 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
        <th class="px-5 py-3 font-medium">Person</th><th class="px-5 py-3 font-medium">Interested in</th><th class="px-5 py-3 font-medium">Came from</th><th class="px-5 py-3 font-medium">Joined</th><th class="px-5 py-3"></th>
      </tr></thead>
      <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
        <template x-for="p in people" :key="p.id">
          <tr class="text-gray-700 dark:text-gray-300">
            <td class="px-5 py-3"><p class="font-medium text-gray-900 dark:text-white" x-text="p.name"></p><p class="text-xs text-gray-500" x-text="p.email + (p.phone ? ' · ' + p.phone : '')"></p></td>
            <td class="whitespace-nowrap px-5 py-3" x-text="p.intent"></td>
            <td class="whitespace-nowrap px-5 py-3 capitalize" x-text="p.source"></td>
            <td class="whitespace-nowrap px-5 py-3" x-text="p.joined"></td>
            <td class="px-5 py-3 text-right">
              <button type="button" :disabled="busy === p.id"
                      @click="busy = p.id; sharet.send('POST', '{{ url('admin/waiting-list') }}/' + p.id + '/invite').then(() => people = people.filter(x => x.id !== p.id)).catch(() => {}).finally(() => busy = null)"
                      class="h-10 rounded-lg bg-violet-600 px-4 text-sm font-medium text-white hover:bg-violet-700 disabled:opacity-50">Invite</button>
            </td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</div>
@endsection
