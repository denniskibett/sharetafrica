<div class="grid gap-6 lg:grid-cols-4">
    @foreach ([
        ['label' => 'Today\'s Sales',    'value' => 'KES ' . number_format($stats['todaySales'] ?? 0, 0),    'route' => 'merchant.accept'],
        ['label' => 'Pending Settlement','value' => 'KES ' . number_format($stats['pending'] ?? 0, 0),       'route' => 'merchant.settle'],
        ['label' => 'QR Codes',          'value' => $stats['qrCodes'] ?? 0,                                 'route' => 'merchant.qr'],
        ['label' => 'Reconciliation',    'value' => $stats['pendingRecon'] ?? 0,                            'route' => 'merchant.reconcile'],
    ] as $card)
        <a href="{{ route($card['route']) }}"
           class="rounded-xl border border-gray-200 bg-white p-5 transition hover:border-primary-30 hover:bg-primary-10
                  dark:border-gray-700 dark:bg-gray-900">
            <p class="text-xs uppercase tracking-widest text-gray-400">{{ $card['label'] }}</p>
            <p class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white">{{ $card['value'] }}</p>
        </a>
    @endforeach
</div>