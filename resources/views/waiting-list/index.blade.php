{{-- Admin: people waiting for a lane. Assigning one emails them (WaitingListController@assign). --}}
@extends('layouts.app')
@section('title', 'Waiting list')

@section('content')
<div x-data="{ people: @js($people->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'email' => $p->email, 'phone' => $p->phone, 'joined' => $p->created_at?->timezone('Africa/Nairobi')->format('j M Y')])), lane: {} }">
  <div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Waiting list</h1>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">People who signed up and are waiting for access. Choose a lane to open their account; they get an email straight away.</p>
  </div>

  <p x-show="!people.length" class="rounded-2xl border border-dashed border-gray-300 p-10 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">Nobody is waiting right now.</p>

  <div x-show="people.length" class="overflow-x-auto rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
    <table class="min-w-full text-left text-sm">
      <thead><tr class="border-b border-gray-100 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
        <th class="px-5 py-3 font-medium">Person</th><th class="px-5 py-3 font-medium">Phone</th><th class="px-5 py-3 font-medium">Joined</th><th class="px-5 py-3 font-medium">Open account as</th>
      </tr></thead>
      <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
        <template x-for="p in people" :key="p.id">
          <tr class="text-gray-700 dark:text-gray-300">
            <td class="px-5 py-3"><p class="font-medium text-gray-900 dark:text-white" x-text="p.name"></p><p class="text-xs text-gray-500" x-text="p.email"></p></td>
            <td class="whitespace-nowrap px-5 py-3" x-text="p.phone || '—'"></td>
            <td class="whitespace-nowrap px-5 py-3" x-text="p.joined"></td>
            <td class="px-5 py-3">
              <form class="flex gap-2" @submit.prevent="sharet.send('POST', '{{ url('admin/waiting-list') }}/' + p.id + '/assign', { lane: lane[p.id] }).then(() => people = people.filter(x => x.id !== p.id)).catch(() => {})">
                <select x-model="lane[p.id]" required class="h-10 rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
                  <option value="">Choose…</option>
                  @foreach ($lanes as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                </select>
                <button class="h-10 rounded-lg bg-violet-600 px-4 text-sm font-medium text-white hover:bg-violet-700">Open account</button>
              </form>
            </td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</div>
@endsection
