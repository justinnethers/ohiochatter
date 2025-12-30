{{-- resources/views/ohio/counties/show.blade.php --}}
<x-app-layout>
    <x-slot name="title">{{ $county->name }} County</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-white leading-tight flex items-center gap-3">
                <span class="hidden md:inline-block w-1 h-6 bg-accent-500 rounded-full"></span>
                {{ $county->name }} County
            </h2>
        </div>
    </x-slot>

    <div class="container mx-auto">
        <div class="md:rounded-2xl md:bg-gradient-to-br md:from-steel-800/50 md:to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-2 md:p-8 md:mt-4">
            <x-breadcrumbs :items="[
                ['title' => 'Ohio', 'url' => route('ohio.index')],
                ['title' => $region->name, 'url' => route('region.show', $region)],
                ['title' => $county->name . ' County'],
            ]"/>

            {{-- Hero Section --}}
            <div class="bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl p-6 md:p-8 mb-6 shadow-lg shadow-black/20 border border-steel-700/50 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-accent-500/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="relative">
                    <div class="flex items-center gap-2 text-sm text-steel-400 mb-2">
                        <a href="{{ route('region.show', $region) }}" class="hover:text-accent-400 transition-colors">{{ $region->name }}</a>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                    <h1 class="text-3xl md:text-4xl font-bold text-white mb-3">{{ $county->name }} County</h1>
                    @if($county->description)
                        <p class="text-steel-300 text-lg max-w-3xl">{{ $county->description }}</p>
                    @endif

                    {{-- Quick Facts --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                        @if($county->county_seat)
                        <div class="bg-steel-900/50 rounded-lg p-4">
                            <div class="text-xs text-steel-500 uppercase tracking-wide mb-1">County Seat</div>
                            <div class="text-lg font-semibold text-white">{{ $county->county_seat }}</div>
                        </div>
                        @endif
                        @if($county->founded_year)
                        <div class="bg-steel-900/50 rounded-lg p-4">
                            <div class="text-xs text-steel-500 uppercase tracking-wide mb-1">Established</div>
                            <div class="text-lg font-semibold text-white">{{ $county->founded_year }}</div>
                        </div>
                        @endif
                        <div class="bg-steel-900/50 rounded-lg p-4">
                            <div class="text-xs text-steel-500 uppercase tracking-wide mb-1">Cities</div>
                            <div class="text-lg font-semibold text-accent-400">{{ $cities->count() }}</div>
                        </div>
                        @if($featuredContent->count() + ($cityContent?->count() ?? 0) > 0)
                        <div class="bg-steel-900/50 rounded-lg p-4">
                            <div class="text-xs text-steel-500 uppercase tracking-wide mb-1">Local Guides</div>
                            <div class="text-lg font-semibold text-accent-400">{{ $featuredContent->count() + ($cityContent?->count() ?? 0) }}</div>
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
                        <a href="{{ route('guide.county.category', ['region' => $region, 'county' => $county, 'category' => $category]) }}"
                           class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium text-steel-300 hover:text-white bg-steel-800/50 hover:bg-accent-500/20 border border-steel-700/50 hover:border-accent-500/50 transition-all duration-200">
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- Cities Grid --}}
            <div class="mb-8">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-white">Cities & Towns</h2>
                    <span class="text-sm text-steel-500">{{ $cities->count() }} total</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($cities as $city)
                        <a href="{{ route('city.show', ['region' => $region, 'county' => $county, 'city' => $city]) }}" class="group bg-gradient-to-br from-steel-800 to-steel-850 p-5 text-steel-100 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50 hover:border-steel-600 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 relative overflow-hidden">
                            <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-accent-400 to-accent-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-white group-hover:text-accent-400 transition-colors">
                                        {{ $city->name }}
                                    </h3>
                                    @if($city->is_major)
                                        <span class="text-xs text-accent-400">Major City</span>
                                    @endif
                                </div>
                                @if($city->content_count > 0)
                                    <span class="text-xs bg-accent-500/20 text-accent-400 px-2 py-0.5 rounded-full">
                                        {{ $city->content_count }} {{ Str::plural('guide', $city->content_count) }}
                                    </span>
                                @endif
                            </div>

                            @if($city->description)
                                <p class="text-steel-400 text-sm line-clamp-2 mt-2">{{ Str::limit($city->description, 100) }}</p>
                            @endif

                            @if($city->population)
                                <div class="mt-3 text-xs text-steel-500">
                                    Pop. {{ number_format($city->population) }}
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
                        <a href="{{ route('guide.county', ['region' => $region, 'county' => $county]) }}" class="text-accent-400 hover:text-accent-300 text-sm font-medium transition-colors">View all &rarr;</a>
                    </div>
                    @foreach($featuredContent as $content)
                        <x-guide.card :content="$content" />
                    @endforeach
                </section>
            @endif

            {{-- Latest from Cities --}}
            @if(isset($cityContent) && $cityContent->isNotEmpty())
                <section>
                    <h2 class="text-lg font-semibold text-white mb-4">Latest From {{ $county->name }} County</h2>
                    @foreach($cityContent as $content)
                        <x-guide.card :content="$content" />
                    @endforeach
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
