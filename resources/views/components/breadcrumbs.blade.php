@props(['items' => []])

<div class="bg-gradient-to-r from-steel-800/80 to-steel-850/80 rounded-xl p-3 px-4 mb-4 font-headline font-medium text-sm md:text-base text-steel-300 border border-steel-700/30 shadow-lg shadow-black/10">
    <a href="/" class="text-steel-300 hover:text-accent-400 transition-colors">Home</a>
    @foreach($items as $item)
        <span class="text-steel-600 mx-2">&blacktriangleright;</span>
        @if(isset($item['url']))
            <a href="{{ $item['url'] }}" class="text-steel-300 hover:text-accent-400 transition-colors">{{ $item['title'] }}</a>
        @else
            <span class="text-accent-400">{{ $item['title'] }}</span>
        @endif
    @endforeach
</div>
