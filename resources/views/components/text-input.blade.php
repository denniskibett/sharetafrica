@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-[#D4FF3D] focus:ring-[#D4FF3D] rounded-md shadow-sm']) }}>
