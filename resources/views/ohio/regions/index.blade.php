{{-- resources/views/ohio/regions/index.blade.php --}}
<x-app-layout :seo="$seo ?? null">
    <x-slot name="title">Explore Ohio</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-white leading-tight flex items-center gap-3">
                <span class="hidden md:inline-block w-1 h-6 bg-accent-500 rounded-full"></span>
                Explore Ohio
            </h2>
        </div>
    </x-slot>

    <div class="container mx-auto">
        <div class="md:rounded-2xl md:bg-gradient-to-br md:from-steel-800/50 md:to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-2 md:p-8 md:mt-4">
            {{-- Hero Section --}}
            <div class="bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl p-6 md:p-8 mb-6 shadow-lg shadow-black/20 border border-steel-700/50 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-96 h-96 bg-accent-500/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="relative">
                    <h1 class="text-3xl md:text-4xl font-bold text-white mb-3">Discover Ohio</h1>
                    <p class="text-steel-300 text-lg max-w-2xl mb-4">From the shores of Lake Erie to the hills of Appalachia, explore communities across the Buckeye State.</p>

                    <a href="{{ route('guide.index') }}" class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium bg-accent-500/10 text-accent-400 hover:bg-accent-500/20 border border-accent-500/30 hover:border-accent-500/50 transition-all duration-200 mb-6">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        Browse All Guides
                    </a>

                    {{-- Quick Stats --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-2xl">
                        <div class="bg-steel-900/50 rounded-lg p-4 text-center">
                            <div class="text-2xl md:text-3xl font-bold text-accent-400">{{ $regions->count() }}</div>
                            <div class="text-sm text-steel-400">Regions</div>
                        </div>
                        <div class="bg-steel-900/50 rounded-lg p-4 text-center">
                            <div class="text-2xl md:text-3xl font-bold text-accent-400">{{ $countyCount }}</div>
                            <div class="text-sm text-steel-400">Counties</div>
                        </div>
                        <div class="bg-steel-900/50 rounded-lg p-4 text-center">
                            <div class="text-2xl md:text-3xl font-bold text-accent-400">{{ $cityCount }}</div>
                            <div class="text-sm text-steel-400">Cities</div>
                        </div>
                        <div class="bg-steel-900/50 rounded-lg p-4 text-center">
                            <div class="text-2xl md:text-3xl font-bold text-accent-400">{{ $guideCount }}</div>
                            <div class="text-sm text-steel-400">Guides</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Regions Grid --}}
            <h2 class="text-lg font-semibold text-white mb-4">Ohio Regions</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($regions as $region)
                    <a href="{{ route('region.show', $region) }}" class="group bg-gradient-to-br from-steel-800 to-steel-850 p-6 text-steel-100 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50 hover:border-steel-600 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 relative overflow-hidden">
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-accent-400 to-accent-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                        <h3 class="text-xl font-bold text-white group-hover:text-accent-400 transition-colors mb-2">
                            {{ $region->name }}
                        </h3>

                        @if($region->description)
                            <p class="text-steel-400 text-sm line-clamp-2 mb-4">{{ $region->description }}</p>
                        @endif

                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-4 text-steel-500">
                                <span>{{ $region->counties_count ?? $region->counties->count() }} counties</span>
                            </div>
                            @if($region->total_content_count > 0)
                                <span class="text-accent-400 font-medium">
                                    {{ $region->total_content_count }} {{ Str::plural('guide', $region->total_content_count) }}
                                </span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
