@props([
    'color' => 'amber'
])
<div class="px-3 py-0.5 rounded-full shadow-inner bg-{{ $color }}-300 text-{{ $color }}-950">{{ $slot }}</div>

<!-- bg-purple-300 text-purple-950 bg-emerald-300 bg-teal-300 text-emerald-950 text-teal-950 -->
