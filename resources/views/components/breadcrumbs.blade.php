<div class="bg-gradient-to-r from-steel-800/80 to-steel-850/80 rounded-xl p-3 px-4 mb-4 font-headline font-medium text-sm md:text-base text-steel-300 border border-steel-700/30 shadow-lg shadow-black/10">
    @if ($forum)
        <a href="/" class="text-steel-300 hover:text-accent-400 transition-colors">Home</a>
        <span class="text-steel-600 mx-2">&blacktriangleright;</span>
        <a href="/forums" class="text-steel-300 hover:text-accent-400 transition-colors">Forums</a>
        <span class="text-steel-600 mx-2">&blacktriangleright;</span>
        <a href="/forums/{{ $forum->slug }}" class="text-accent-400 hover:text-accent-300 transition-colors">{{ $forum->name }}</a>
    @endif
</div>
