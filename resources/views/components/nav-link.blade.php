@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-[#C8102E] text-sm font-semibold leading-5 text-[#C8102E] focus:outline-none focus:border-[#A00E27] transition duration-150 ease-in-out'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-semibold leading-5 text-gray-700 hover:text-[#C8102E] hover:border-[#C8102E]/40 focus:outline-none focus:text-[#C8102E] focus:border-[#C8102E]/40 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
