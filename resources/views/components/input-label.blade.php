@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-bold text-lg text-gray-300 dark:text-gray-300']) }}>
    {{ $value ?? $slot }}
</label>
