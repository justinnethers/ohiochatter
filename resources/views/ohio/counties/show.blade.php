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

            <div class="bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl p-6 mb-6 shadow-lg shadow-black/20 border border-steel-700/50">
                <h1 class="text-2xl md:text-3xl font-bold text-white mb-3">{{ $county->name }} County</h1>
                @if($county->description)
                    <p class="text-steel-300 text-lg">{{ $county->description }}</p>
                @endif
            </div>

            @if($categories->isNotEmpty())
                <div class="flex flex-wrap items-center gap-2 mb-6">
                    <span class="text-steel-400 font-medium">Browse by category:</span>
                    @foreach($categories as $category)
                        <a href="{{ route('guide.county.category', ['region' => $region, 'county' => $county, 'category' => $category]) }}"
                           class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium text-steel-300 hover:text-white hover:bg-steel-700/50 border border-steel-700/50 hover:border-steel-600 transition-all duration-200">
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            @endif

            <h2 class="text-lg font-semibold text-white mb-4">Cities in {{ $county->name }} County</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
                @foreach($cities as $city)
                    <a href="{{ route('city.show', ['region' => $region, 'county' => $county, 'city' => $city]) }}" class="group bg-gradient-to-br from-steel-800 to-steel-850 p-5 text-steel-100 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50 hover:border-steel-600 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 relative overflow-hidden">
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-accent-400 to-accent-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                        <h3 class="text-lg font-semibold text-white group-hover:text-accent-400 transition-colors mb-2">
                            {{ $city->name }}
                        </h3>

                        @if($city->description)
                            <p class="text-steel-400 text-sm line-clamp-2 mb-3">{{ Str::limit($city->description, 150) }}</p>
                        @endif

                        @if($city->content_count > 0)
                            <span class="text-sm text-accent-400">
                                {{ $city->content_count }} {{ Str::plural('guide', $city->content_count) }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>

            @if($featuredContent->isNotEmpty())
                <section class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-white">Featured {{ $county->name }} County Guides</h2>
                        <a href="{{ route('guide.county', ['region' => $region, 'county' => $county]) }}" class="text-accent-400 hover:text-accent-300 text-sm font-medium transition-colors">View all &rarr;</a>
                    </div>
                    @foreach($featuredContent as $content)
                        <x-guide.card :content="$content" />
                    @endforeach
                </section>
            @endif

            @if(isset($cityContent) && $cityContent->isNotEmpty())
                <section>
                    <h2 class="text-lg font-semibold text-white mb-4">Latest From {{ $county->name }} County Cities</h2>
                    @foreach($cityContent as $content)
                        <x-guide.card :content="$content" />
                    @endforeach
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
