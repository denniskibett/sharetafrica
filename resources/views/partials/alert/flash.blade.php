{{-- resources/views/home/partials/flash.blade.php --}}
@php
    $flashMap = [
        'waitlist_received' => 'waitlist_message',
        'trade_received'    => 'trade_message',
        'dev_received'      => 'dev_message',
        'contact_received'  => 'contact_message',
    ];
@endphp

@foreach($flashMap as $flag => $messageKey)
    @if(session($flag))
        <div class="container container--wide" style="padding-top: var(--space-6);">
            <div class="flash flash--success" role="status">
                <span class="flash__icon" aria-hidden="true">✓</span>
                <p>{{ session($messageKey) }}</p>
            </div>
        </div>
    @endif
@endforeach

@if($errors->any())
    <div class="container container--wide" style="padding-top: var(--space-6);">
        <div class="flash flash--error" role="alert">
            <span class="flash__icon" aria-hidden="true">!</span>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif