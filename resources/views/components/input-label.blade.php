@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-semibold text-steel-200']) }}>
    {{ $value ?? $slot }}
</label>
