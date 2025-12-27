@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full pl-3 pr-4 py-2 border-l-4 border-accent-500 text-left text-base font-medium text-accent-300 bg-accent-900/30 focus:outline-none focus:text-accent-200 focus:bg-accent-900/50 focus:border-accent-400 transition duration-150 ease-in-out'
            : 'block w-full pl-3 pr-4 py-2 border-l-4 border-transparent text-left text-base font-medium text-steel-300 hover:text-white hover:bg-steel-700/50 hover:border-steel-500 focus:outline-none focus:text-white focus:bg-steel-700/50 focus:border-steel-500 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
