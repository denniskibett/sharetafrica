{{-- Form field: label, input (or slot for select/textarea), help, and the error from `errors[name]`. --}}
@props(['label', 'name', 'type' => 'text', 'model' => null, 'help' => null, 'required' => false])
<div {{ $attributes->only('class') }}>
  <label for="f-{{ $name }}" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}@if ($required)<span class="text-red-500"> *</span>@endif</label>
  @if ($slot->isNotEmpty())
    {{ $slot }}
  @else
    <input id="f-{{ $name }}" name="{{ $name }}" type="{{ $type }}" x-model="{{ $model ?? 'form.'.$name }}" @if ($required) required @endif
           {{ $attributes->except('class') }}
           class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-violet-300 focus:outline-none focus:ring-4 focus:ring-violet-500/10 disabled:bg-gray-50 disabled:text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:disabled:bg-white/5">
  @endif
  @if ($help)<p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $help }}</p>@endif
  <p x-show="errors['{{ $name }}']" x-text="errors['{{ $name }}']" class="mt-1 text-xs text-red-600 dark:text-red-400"></p>
</div>
