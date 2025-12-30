{{-- resources/views/ohio/regions/show.blade.php --}}
<x-app-layout>
    <x-slot name="title">{{ $region->name }}</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-white leading-tight flex items-center gap-3">
                <span class="hidden md:inline-block w-1 h-6 bg-accent-500 rounded-full"></span>
                {{ $region->name }}
            </h2>
        </div>
    </x-slot>

    <div class="container mx-auto">
        <div class="md:rounded-2xl md:bg-gradient-to-br md:from-steel-800/50 md:to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-2 md:p-8 md:mt-4">
            <x-breadcrumbs :items="[
                ['title' => 'Ohio', 'url' => route('ohio.index')],
                ['title' => $region->name],
            ]"/>

            {{-- Hero Section --}}
            <div class="bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl p-6 md:p-8 mb-6 shadow-lg shadow-black/20 border border-steel-700/50 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-accent-500/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="relative">
                    <h1 class="text-3xl md:text-4xl font-bold text-white mb-3">{{ $region->name }}</h1>
                    @if($region->description)
                        <p class="text-steel-300 text-lg max-w-3xl">{{ $region->description }}</p>
                    @endif

                    {{-- Quick Stats --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                        <div class="bg-steel-900/50 rounded-lg p-4 text-center">
                            <div class="text-2xl md:text-3xl font-bold text-accent-400">{{ $counties->count() }}</div>
                            <div class="text-sm text-steel-400">Counties</div>
                        </div>
                        <div class="bg-steel-900/50 rounded-lg p-4 text-center">
                            <div class="text-2xl md:text-3xl font-bold text-accent-400">{{ $counties->sum(fn($c) => $c->cities_count ?? $c->cities->count()) }}</div>
                            <div class="text-sm text-steel-400">Cities</div>
                        </div>
                        @if($featuredContent->count() + ($countyContent?->count() ?? 0) > 0)
                        <div class="bg-steel-900/50 rounded-lg p-4 text-center">
                            <div class="text-2xl md:text-3xl font-bold text-accent-400">{{ $featuredContent->count() + ($countyContent?->count() ?? 0) }}</div>
                            <div class="text-sm text-steel-400">Guides</div>
                        </div>
                        @endif
                        @if($categories->isNotEmpty())
                        <div class="bg-steel-900/50 rounded-lg p-4 text-center">
                            <div class="text-2xl md:text-3xl font-bold text-accent-400">{{ $categories->count() }}</div>
                            <div class="text-sm text-steel-400">Categories</div>
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
                        <a href="{{ route('guide.region.category', ['region' => $region, 'category' => $category]) }}"
                           class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium text-steel-300 hover:text-white bg-steel-800/50 hover:bg-accent-500/20 border border-steel-700/50 hover:border-accent-500/50 transition-all duration-200">
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- Counties Grid --}}
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-white">Counties in {{ $region->name }}</h2>
                    <span class="text-sm text-steel-500">{{ $counties->count() }} total</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($counties as $county)
                        <a href="{{ route('county.show', ['region' => $region, 'county' => $county]) }}" class="group bg-gradient-to-br from-steel-800 to-steel-850 p-5 text-steel-100 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50 hover:border-steel-600 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 relative overflow-hidden">
                            <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-accent-400 to-accent-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                            <div class="flex items-start justify-between">
                                <h3 class="text-lg font-semibold text-white group-hover:text-accent-400 transition-colors">
                                    {{ $county->name }} County
                                </h3>
                                @if($county->content_count > 0)
                                    <span class="text-xs bg-accent-500/20 text-accent-400 px-2 py-0.5 rounded-full">
                                        {{ $county->content_count }} {{ Str::plural('guide', $county->content_count) }}
                                    </span>
                                @endif
                            </div>

                            @if($county->description)
                                <p class="text-steel-400 text-sm line-clamp-2 mt-2">{{ Str::limit($county->description, 120) }}</p>
                            @endif

                            @if($county->county_seat || $county->founded_year)
                                <div class="flex items-center gap-4 mt-3 text-xs text-steel-500">
                                    @if($county->county_seat)
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                            {{ $county->county_seat }}
                                        </span>
                                    @endif
                                    @if($county->founded_year)
                                        <span>Est. {{ $county->founded_year }}</span>
                                    @endif
                                </div>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Featured Guides --}}
            @if($featuredContent->isNotEmpty())
                <section class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-accent-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            Featured Guides
                        </h2>
                        <a href="{{ route('guide.region', $region) }}" class="text-accent-400 hover:text-accent-300 text-sm font-medium transition-colors">View all &rarr;</a>
                    </div>
                    @foreach($featuredContent as $content)
                        <x-guide.card :content="$content" />
                    @endforeach
                </section>
            @endif

            {{-- Latest from Counties --}}
            @if(isset($countyContent) && $countyContent->isNotEmpty())
                <section>
                    <h2 class="text-lg font-semibold text-white mb-4">Latest From {{ $region->name }} Counties</h2>
                    @foreach($countyContent as $content)
                        <x-guide.card :content="$content" />
                    @endforeach
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
