@props([
    'color' => 'gray',
    'shade' => '900',
    'space' => '4'
])
<div
    class="relative rounded-xl p-6 shadow-sm ring-1 bg-{{ $color }}-{{ $shade }} ring-white/10 space-y-{{ $space }} text-{{ $color }}-{{ $shade === '900' ? '100' : '900' }}">
    {{ $slot }}
</div>
<!-- bg-red-900 bg-red-100 bg-red-200 text-red-900 -->
