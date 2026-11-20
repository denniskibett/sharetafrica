{{-- Server-rendered form field (classic POST forms: sign-in, sign-up, onboarding). Label, filled-grey input (or a
     <select>/<textarea> when `as` says so, options or text in the slot), hint, and the Laravel error for `name`.
     Pass any input attributes (required, autocomplete, inputmode, placeholder...) straight through. --}}
@props(['label', 'name', 'type' => 'text', 'value' => null, 'hint' => null, 'as' => 'input', 'id' => null])
@php
    $id = $id ?? $name;
    $key = str_replace(['[]', '[', ']'], ['', '.', ''], $name);
    $bad = $errors->has($key);
    $look = 'w-full rounded-2xl border-0 bg-app-tile px-4 text-base text-app-ink placeholder:text-app-faint focus:outline-none focus:ring-2 '
        .($bad ? 'ring-2 ring-red-500 focus:ring-red-500' : 'focus:ring-app-ink/70');
@endphp
<div {{ $attributes->only('class') }}>
  <label for="{{ $id }}" class="mb-1.5 block text-[13px] font-medium text-app-muted">{{ $label }}</label>
  @if ($as === 'select')
    <select id="{{ $id }}" name="{{ $name }}" {{ $attributes->except('class') }} @if ($bad) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
            class="{{ $look }} {{ $attributes->has('multiple') ? 'py-2' : 'h-12' }}">{{ $slot }}</select>
  @elseif ($as === 'textarea')
    <textarea id="{{ $id }}" name="{{ $name }}" {{ $attributes->except('class') }} @if ($bad) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
              class="{{ $look }} py-3">{{ $slot }}</textarea>
  @else
    <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" @if (! is_null($value)) value="{{ $value }}" @endif {{ $attributes->except('class') }}
           @if ($bad) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif class="{{ $look }} h-12">
  @endif
  @if ($hint && ! $bad)<p class="mt-1.5 text-xs text-app-muted">{{ $hint }}</p>@endif
  @error($key)<p id="{{ $id }}-error" class="mt-1.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
</div>
