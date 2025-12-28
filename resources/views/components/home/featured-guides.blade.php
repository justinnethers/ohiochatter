@props(['guides'])

<div class="bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl border border-steel-700/50 p-4">
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-sm font-semibold text-white flex items-center gap-2">
            <span class="w-1 h-4 bg-accent-500 rounded-full"></span>
            Ohio Guides
        </h3>
        <a href="{{ route('guide.index') }}" class="text-xs text-accent-400 hover:text-accent-300">
            View All &rarr;
        </a>
    </div>

    <div class="space-y-3">
        @foreach($guides as $guide)
            <a href="{{ route('guide.show', $guide) }}"
               class="block group p-3 bg-steel-900/50 rounded-lg hover:bg-steel-900 transition-colors">
                <h4 class="text-sm font-medium text-steel-200 group-hover:text-white line-clamp-2 mb-1">
                    {{ $guide->title }}
                </h4>
                @if($guide->locatable)
                    <div class="flex items-center gap-1 text-xs text-steel-500">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        {{ $guide->locatable->name }}
                    </div>
                @endif
                @if($guide->contentCategory)
                    <span class="inline-block mt-1 text-xs text-accent-400/80 bg-accent-500/10 px-2 py-0.5 rounded">
                        {{ $guide->contentCategory->name }}
                    </span>
                @endif
            </a>
        @endforeach
    </div>
</div>
