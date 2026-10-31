{{-- Page title + breadcrumb + action buttons slot. --}}
@props(['title', 'subtitle' => null])
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
  <div>
    <nav class="mb-1 text-xs text-gray-500 dark:text-gray-400" aria-label="Breadcrumb">
      <a href="{{ route('dashboard') }}" class="hover:text-gray-800 dark:hover:text-white">Dashboard</a>
      <span class="mx-1">/</span><span class="text-gray-700 dark:text-gray-300">{{ $title }}</span>
    </nav>
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $title }}</h1>
    @if ($subtitle)<p class="mt-1 max-w-3xl text-sm text-gray-500 dark:text-gray-400">{{ $subtitle }}</p>@endif
  </div>
  @if ($slot->isNotEmpty())<div class="flex flex-wrap items-center gap-2">{{ $slot }}</div>@endif
</div>
