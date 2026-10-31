{{-- Enquiries from sharet.africa/contact. Data: EmailDesignController@messages as JSON; open one to read it in the slide-over. --}}
@extends('layouts.app')
@section('title', 'Enquiries')

@section('content')
<div x-data="dataTable({ url: '{{ route('admin.contact.index') }}', filters: { audience: '' } })">
  <x-ui.page-header title="Enquiries" subtitle="Messages from the contact form on sharet.africa. Each one is also emailed to the team inbox." />

  <x-ui.stats :cols="4" class="mb-6">
    <x-ui.stat label="Enquiries" value="stats.total" hint="stats.total_hint" icon="inbox-arrow-down" tone="sky" />
    <x-ui.stat label="This week" value="stats.week" hint="stats.week_hint" icon="sparkles" tone="violet" />
    <x-ui.stat label="Open" value="stats.open" hint="stats.open_hint" icon="clock" tone="amber" />
    <x-ui.stat label="Answered" value="stats.answered" hint="stats.answered_hint" icon="check-badge" tone="emerald" />
  </x-ui.stats>

  <x-ui.table title="Inbox" placeholder="Search people, business or message…" :columns="[
      ['key' => 'person', 'label' => 'From', 'type' => 'person'],
      ['key' => 'topic', 'label' => 'About'],
      ['key' => 'business', 'label' => 'Business', 'type' => 'muted'],
      ['key' => 'status', 'label' => 'Status', 'type' => 'pill'],
      ['key' => 'date', 'label' => 'Received', 'type' => 'muted', 'sort' => 'date'],
  ]">
    <x-slot:filters>
      <select x-model="filters.audience" aria-label="Topic" class="h-10 rounded-lg border border-gray-300 bg-transparent pl-3 pr-9 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
        <option value="">All topics</option>
        <template x-for="(label, key) in extra.audiences" :key="key"><option :value="key" x-text="label"></option></template>
      </select>
    </x-slot:filters>
    <x-slot:actions>
      <x-ui.button variant="secondary" class="!h-9" icon="eye" @click="sharet.panel('enquiry', row)">Read</x-ui.button>
    </x-slot:actions>
    <x-slot:empty>No messages yet.</x-slot:empty>
  </x-ui.table>

  <x-ui.slide-over name="enquiry" title="Enquiry">
    <div class="space-y-5 text-sm">
      <div>
        <p class="font-medium text-gray-900 dark:text-white" x-text="payload.person?.name"></p>
        <p class="text-gray-500 dark:text-gray-400" x-text="payload.person?.email"></p>
      </div>
      <dl class="grid grid-cols-[120px_1fr] gap-x-4 gap-y-2">
        <dt class="text-gray-500">About</dt><dd class="text-gray-800 dark:text-gray-200" x-text="payload.topic"></dd>
        <dt class="text-gray-500">Business</dt><dd class="text-gray-800 dark:text-gray-200" x-text="payload.business || '—'"></dd>
        <dt class="text-gray-500">Phone</dt><dd class="text-gray-800 dark:text-gray-200" x-text="payload.phone || '—'"></dd>
        <dt class="text-gray-500">Country</dt><dd class="text-gray-800 dark:text-gray-200" x-text="payload.country || '—'"></dd>
        <dt class="text-gray-500">Received</dt><dd class="text-gray-800 dark:text-gray-200" x-text="payload.date"></dd>
      </dl>
      <p class="whitespace-pre-line rounded-xl bg-gray-50 p-4 text-gray-800 dark:bg-white/5 dark:text-gray-200" x-text="payload.brief"></p>
    </div>
    <x-slot:footer>
      <x-ui.button icon="arrow-uturn-left" x-bind:href="'mailto:' + (payload.person?.email || '') + '?subject=' + encodeURIComponent('Re: your note to Sharet')">Reply by email</x-ui.button>
    </x-slot:footer>
  </x-ui.slide-over>
</div>
@endsection
