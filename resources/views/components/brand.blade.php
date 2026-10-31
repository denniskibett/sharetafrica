{{-- Sharet logo: the website's lime mark + wordmark. `mark-only` for the collapsed sidebar; text follows light/dark. --}}
@props(['size' => 32, 'markOnly' => false])
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 28 28" aria-hidden="true" class="shrink-0">
        <rect width="28" height="28" rx="5" fill="#D4FF3D"/>
        <path d="M6 6h16v9.6L14 22l-8-6.4z" fill="#050507"/>
    </svg>
    @unless ($markOnly)
        <span class="font-outfit text-xl font-extrabold uppercase tracking-tight text-gray-900 dark:text-white">Sharet<span class="text-lime-500 dark:text-[#D4FF3D]">!</span></span>
    @endunless
</span>
