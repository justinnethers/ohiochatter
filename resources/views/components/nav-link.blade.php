@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-1 pt-1 border-b-2 border-accent-500 text-sm font-semibold leading-5 text-white tracking-wide focus:outline-none transition-all duration-200'
            : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-steel-300 tracking-wide hover:text-white hover:border-accent-400/50 focus:outline-none focus:text-white transition-all duration-200';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
