<x-app-layout :seo="$seo ?? null">
    <x-slot name="title">All Forums</x-slot>
    <x-slot name="header">
        <h2 class="text-lg md:text-xl font-bold text-white leading-tight flex items-center gap-3">
            <span class="hidden md:inline-block w-1 h-6 bg-accent-500 rounded-full"></span>
            All Forums
        </h2>
    </x-slot>

    <div class="container mx-auto">
        <div class="md:rounded-2xl md:bg-gradient-to-br md:from-steel-800/50 md:to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-2 md:p-8 md:pt-6 md:mt-4">
            <div class="space-y-3">
                @foreach($forums as $forum)
                    <a href="/forums/{{ $forum->slug }}"
                       class="group block bg-gradient-to-br from-steel-800 to-steel-850 p-4 md:p-5 rounded-xl border border-steel-700/50 hover:border-steel-600 shadow-lg shadow-black/20 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 relative overflow-hidden">
                        {{-- Accent stripe on left --}}
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-accent-400 to-accent-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-{{ $forum->color }}-500/20 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-{{ $forum->color }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-white group-hover:text-accent-400 transition-colors">{{ $forum->name }}</h3>
                                    @if($forum->description)
                                        <p class="text-steel-400 text-sm mt-0.5">{{ $forum->description }}</p>
                                    @endif
                                </div>
                            </div>
                            <svg class="w-5 h-5 text-steel-500 group-hover:text-accent-400 group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
