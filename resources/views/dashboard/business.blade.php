<div class="grid gap-6 lg:grid-cols-4">
    @foreach ([
        ['label' => 'Open Invoices',   'value' => $stats['openInvoices'] ?? 0,    'route' => 'business.invoices.index'],
        ['label' => 'Pay Suppliers',   'value' => $stats['suppliers'] ?? 0,       'route' => 'business.pay'],
        ['label' => 'Trade Finance',   'value' => $stats['tradeFinance'] ?? 0,    'route' => 'business.trade-finance'],
        ['label' => 'Treasury',        'value' => $stats['treasury'] ?? 0,        'route' => 'business.treasury'],
    ] as $card)
        <a href="{{ route($card['route']) }}"
           class="rounded-xl border border-gray-200 bg-white p-5 transition hover:border-primary-30 hover:bg-primary-10
                  dark:border-gray-700 dark:bg-gray-900">
            <p class="text-xs uppercase tracking-widest text-gray-400">{{ $card['label'] }}</p>
            <p class="mt-2 text-3xl font-semibold text-gray-800 dark:text-white">{{ $card['value'] }}</p>
        </a>
    @endforeach
</div>