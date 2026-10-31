@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 bg-white text-gray-900 focus:border-violet-500 focus:ring-violet-500 rounded-lg shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90']) }}>
