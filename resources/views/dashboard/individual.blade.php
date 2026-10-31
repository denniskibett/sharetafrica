<div class="grid gap-6 lg:grid-cols-3">
    @foreach ([
        ['label' => 'Send',    'value' => 'Send money',    'route' => 'wallet.transfer'],
        ['label' => 'Receive', 'value' => 'Receive money', 'route' => 'wallet.deposit'],
        ['label' => 'QR',      'value' => 'My QR code',    'route' => 'individual.dashboard'],
    ] as $card)
        <a href="{{ route($card['route']) }}"
           class="rounded-xl border border-gray-200 bg-white p-6 transition hover:border-primary-30 hover:bg-primary-10
                  dark:border-gray-700 dark:bg-gray-900">
            <p class="text-xs uppercase tracking-widest text-gray-400">{{ $card['label'] }}</p>
            <p class="mt-2 text-lg font-semibold text-gray-800 dark:text-white">{{ $card['value'] }}</p>
        </a>
    @endforeach
</div>