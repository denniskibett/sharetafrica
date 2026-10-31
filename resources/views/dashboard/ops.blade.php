<div class="grid gap-6 lg:grid-cols-4">
    @foreach ([
        ['label' => 'Reconciliation', 'value' => $stats['reconciliation'] ?? 0, 'route' => 'ops.reconciliation.index'],
        ['label' => 'Treasury Pools', 'value' => $stats['treasury']       ?? 0, 'route' => 'ops.treasury.index'],
        ['label' => 'Live Rails',     'value' => $stats['rails']          ?? 0, 'route' => 'ops.rails.index'],
        ['label' => 'Open Tickets',   'value' => $stats['tickets']        ?? 0, 'route' => 'ops.support.index'],
    ] as $card)
        <a href="{{ route($card['route']) }}"
           class="rounded-xl border border-gray-200 bg-white p-5 transition hover:border-primary-30 hover:bg-primary-10
                  dark:border-gray-700 dark:bg-gray-900">
            <p class="text-xs uppercase tracking-widest text-gray-400">{{ $card['label'] }}</p>
            <p class="mt-2 text-3xl font-semibold text-gray-800 dark:text-white">{{ $card['value'] }}</p>
        </a>
    @endforeach
</div>