@props(['disabled' => false])

<select
    {{ $disabled ? 'disabled' : '' }}
    {!! $attributes->merge(['class' => 'border border-steel-600 bg-steel-950 text-steel-100 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg shadow-inner p-2.5 px-4 text-base w-full transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed']) !!}
>
    {{ $slot }}
</select>
