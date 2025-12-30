{{-- resources/views/ohio/cities/show.blade.php --}}
<x-app-layout>
    <x-slot name="title">{{ $city->name }}, Ohio</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-white leading-tight flex items-center gap-3">
                <span class="hidden md:inline-block w-1 h-6 bg-accent-500 rounded-full"></span>
                {{ $city->name }}, Ohio
            </h2>
        </div>
    </x-slot>

    <div class="container mx-auto">
        <div
            class="md:rounded-2xl md:bg-gradient-to-br md:from-steel-800/50 md:to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-2 md:p-8 md:mt-4">
            <x-breadcrumbs :items="[
                ['title' => 'Ohio', 'url' => route('ohio.index')],
                ['title' => $region->name, 'url' => route('region.show', $region)],
                ['title' => $county->name . ' County', 'url' => route('county.show', ['region' => $region, 'county' => $county])],
                ['title' => $city->name],
            ]"/>

            {{-- Hero Section --}}
            <div
                class="bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl p-6 md:p-8 mb-6 shadow-lg shadow-black/20 border border-steel-700/50 relative overflow-hidden">
                <div
                    class="absolute top-0 right-0 w-64 h-64 bg-accent-500/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="relative">
                    <div class="flex items-center gap-2 text-sm text-steel-400 mb-2">
                        <a href="{{ route('county.show', ['region' => $region, 'county' => $county]) }}"
                           class="hover:text-accent-400 transition-colors">{{ $county->name }} County</a>
                        <span class="text-steel-600">&bull;</span>
                        <a href="{{ route('region.show', $region) }}"
                           class="hover:text-accent-400 transition-colors">{{ $region->name }}</a>
                    </div>
                    <h1 class="text-3xl md:text-4xl font-bold text-white mb-1">{{ $city->name }}</h1>

                    @if($city->description)
                        <p class="text-steel-300 text-lg max-w-3xl">{{ $city->description }}</p>
                    @endif

                    {{-- City Stats --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                        @if($city->population)
                            <div class="bg-steel-900/50 rounded-lg p-4">
                                <div class="text-xs text-steel-500 uppercase tracking-wide mb-1">Population</div>
                                <div class="text-xl font-bold text-white">{{ number_format($city->population) }}</div>
                            </div>
                        @endif
                        <div class="bg-steel-900/50 rounded-lg p-4">
                            <div class="text-xs text-steel-500 uppercase tracking-wide mb-1">County</div>
                            <div class="text-lg font-semibold text-white">{{ $county->name }}</div>
                        </div>
                        <div class="bg-steel-900/50 rounded-lg p-4">
                            <div class="text-xs text-steel-500 uppercase tracking-wide mb-1">Region</div>
                            <div class="text-lg font-semibold text-white">{{ $region->name }}</div>
                        </div>
                        @if($featuredContent->isNotEmpty())
                            <div class="bg-steel-900/50 rounded-lg p-4">
                                <div class="text-xs text-steel-500 uppercase tracking-wide mb-1">Local Guides</div>
                                <div class="text-xl font-bold text-accent-400">{{ $featuredContent->count() }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Category Pills --}}
            @if($categories->isNotEmpty())
                <div class="flex flex-wrap items-center gap-2 mb-6">
                    <span class="text-steel-400 font-medium text-sm">Explore:</span>
                    @foreach($categories as $category)
                        <a href="{{ route('guide.city.category', ['region' => $region, 'county' => $county, 'city' => $city, 'category' => $category]) }}"
                           class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium text-steel-300 hover:text-white bg-steel-800/50 hover:bg-accent-500/20 border border-steel-700/50 hover:border-accent-500/50 transition-all duration-200">
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- Featured Guides --}}
            @if($featuredContent->isNotEmpty())
                <section>
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            Local Guides
                        </h2>
                        <a href="{{ route('guide.city', ['region' => $region, 'county' => $county, 'city' => $city]) }}"
                           class="text-accent-400 hover:text-accent-300 text-sm font-medium transition-colors">View all
                            &rarr;</a>
                    </div>
                    @foreach($featuredContent as $content)
                        <x-guide.card :content="$content"/>
                    @endforeach
                </section>
            @else
                {{-- Empty State with CTA --}}
                <div
                    class="bg-gradient-to-br from-steel-800 to-steel-850 p-8 text-center rounded-xl shadow-lg shadow-black/20 border border-steel-700/50">
                    <svg class="w-16 h-16 mx-auto mb-4 text-steel-600" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <h3 class="text-xl font-semibold text-white mb-2">No guides yet for {{ $city->name }}</h3>
                    <p class="text-steel-400 mb-6 max-w-md mx-auto">Be the first to share your knowledge about this
                        area. Help others discover what makes {{ $city->name }} special.</p>
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <a href="{{ route('guide.county', ['region' => $region, 'county' => $county]) }}"
                           class="inline-flex items-center justify-center px-4 py-2 rounded-lg text-steel-300 bg-steel-700/50 hover:bg-steel-700 border border-steel-600 transition-colors">
                            Browse {{ $county->name }} County Guides
                        </a>
                    </div>
                </div>
            @endif

            {{-- Nearby Cities (optional enhancement) --}}
            @if(isset($nearbyCities) && $nearbyCities->isNotEmpty())
                <section class="mt-8">
                    <h2 class="text-lg font-semibold text-white mb-4">Nearby Cities</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach($nearbyCities as $nearbyCity)
                            <a href="{{ route('city.show', ['region' => $region, 'county' => $county, 'city' => $nearbyCity]) }}"
                               class="bg-steel-800/50 hover:bg-steel-800 border border-steel-700/50 hover:border-steel-600 rounded-lg p-3 text-center transition-all">
                                <div class="font-medium text-white">{{ $nearbyCity->name }}</div>
                                @if($nearbyCity->population)
                                    <div class="text-xs text-steel-500">
                                        Pop. {{ number_format($nearbyCity->population) }}</div>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
