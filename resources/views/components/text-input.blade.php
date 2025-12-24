@props(['disabled' => false])

<input
    {{ $disabled ? 'disabled' : '' }}
    {!! $attributes->merge(['class' => 'border-gray-600 dark:border-gray-600 bg-gray-700 dark:bg-gray-700 text-gray-200 dark:text-gray-200 focus:border-indigo-500 dark:focus:border-indigo-500 focus:ring-indigo-500 dark:focus:ring-indigo-500 rounded-md shadow-sm p-1.5 px-3 text-lg w-full font-medium']) !!}>
