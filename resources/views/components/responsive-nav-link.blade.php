@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full pl-3 pr-4 py-2 border-l-4 border-blue-600 dark:border-blue-600 text-left text-base font-medium text-blue-300 dark:text-blue-300 bg-blue-900/50 dark:bg-blue-900/50 focus:outline-none focus:text-blue-200 dark:focus:text-blue-200 focus:bg-blue-900 dark:focus:bg-blue-900 focus:border-blue-300 dark:focus:border-blue-300 transition duration-150 ease-in-out'
            : 'block w-full pl-3 pr-4 py-2 border-l-4 border-transparent text-left text-base font-medium text-gray-400 dark:text-gray-400 hover:text-gray-200 dark:hover:text-gray-200 hover:bg-gray-700 dark:hover:bg-gray-700 hover:border-gray-600 dark:hover:border-gray-600 focus:outline-none focus:text-gray-200 dark:focus:text-gray-200 focus:bg-gray-50 dark:focus:bg-gray-700 focus:border-gray-600 dark:focus:border-gray-600 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
