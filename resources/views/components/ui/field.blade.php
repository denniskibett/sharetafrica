{{-- Form field: label, input (or slot for select/textarea), help, and the error from `errors[name]`. --}}
@props(['label', 'name', 'type' => 'text', 'model' => null, 'help' => null, 'required' => false])
<div {{ $attributes->only('class') }}>
  <label for="f-{{ $name }}" class="mb-1.5 block text-[13px] font-medium text-gray-500 dark:text-gray-400">{{ $label }}@if ($required)<span class="text-red-500"> *</span>@endif</label>
  @if ($slot->isNotEmpty())
    {{ $slot }}
  @else
    <input id="f-{{ $name }}" name="{{ $name }}" type="{{ $type }}" x-model="{{ $model ?? 'form.'.$name }}" @if ($required) required @endif
           {{ $attributes->except('class') }}
           class="ui-input h-12 w-full rounded-2xl border-0 bg-gray-100 px-4 text-[15px] text-gray-800 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-900/70 disabled:text-gray-500 dark:bg-white/[0.06] dark:text-white/90 dark:focus:ring-[#D4FF3D]/70">
  @endif
  @if ($help)<p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $help }}</p>@endif
  <p x-show="errors['{{ $name }}']" x-text="errors['{{ $name }}']" class="mt-1 text-xs text-red-600 dark:text-red-400"></p>
</div>
