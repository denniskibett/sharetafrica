<div class="grid gap-6 lg:grid-cols-4">
    @foreach ([
        ['label' => 'API Keys',      'value' => $stats['apiKeys'] ?? 0,   'route' => 'developer.api-keys.index'],
        ['label' => 'Webhooks',      'value' => $stats['webhooks'] ?? 0,  'route' => 'developer.webhooks.index'],
        ['label' => 'Requests Today','value' => $stats['requests'] ?? 0,  'route' => 'developer.logs'],
        ['label' => 'Sandbox',       'value' => 'Live',                   'route' => 'developer.sandbox'],
    ] as $card)
        <a href="{{ route($card['route']) }}"
           class="rounded-xl border border-gray-200 bg-white p-5 transition hover:border-primary-30 hover:bg-primary-10
                  dark:border-gray-700 dark:bg-gray-900">
            <p class="text-xs uppercase tracking-widest text-gray-400">{{ $card['label'] }}</p>
            <p class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white">{{ $card['value'] }}</p>
        </a>
    @endforeach
</div>