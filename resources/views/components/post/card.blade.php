@props(['id' => null])

<article @if($id) id="{{ $id }}" @endif class="bg-gradient-to-br from-steel-800 to-steel-850 text-white mb-5 md:flex rounded-xl relative border border-steel-700/50 shadow-xl shadow-black/20 overflow-hidden">
    {{-- Subtle top accent --}}
    <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-steel-600/50 to-transparent"></div>

    {{ $slot }}
</article>
