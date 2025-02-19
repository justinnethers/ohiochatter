{{-- resources/views/ohio/counties/show.blade.php --}}
<x-app-layout>
    <x-slot name="title">{{ $county->name }}</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-3xl text-gray-200 dark:text-gray-200 leading-tight">
            {{ $county->name }}
        </h2>
    </x-slot>
    <div>
        <div class="md:rounded-lg md:bg-gray-800 p-2 md:p-8 md:pt-4 md:mt-4">
            <div class="bg-gray-700 rounded-lg p-6 mb-8">
                <div class="flex items-center gap-2 text-sm text-gray-400 mb-4">
                    <a href="{{ route('region.show', $region) }}" class="hover:text-white">{{ $region->name }}</a>
                    <span>&raquo;</span>
                    <span class="font-medium text-white">{{ $county->name }} County</span>
                </div>
                <h1 class="text-3xl font-bold text-gray-100 mb-4">{{ $county->name }} County</h1>
                <p class="text-gray-300 text-lg">{{ $county->description }}</p>
            </div>

            {{-- Category filters --}}
            @if($categories->isNotEmpty())
                <div class="flex flex-wrap gap-2 mb-6">
                    <h3 class="text-xl font-semibold text-gray-200 mr-4 flex items-center">Browse by category:</h3>
                    @foreach($categories as $category)
                        <a href="{{ route('guide.county.category', ['region' => $region, 'county' => $county, 'category' => $category]) }}"
                           class="inline-block px-4 py-2 rounded-full text-sm font-medium
                                  text-gray-400 hover:text-white hover:bg-gray-700
                                  transition duration-150 ease-in-out">
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- Cities --}}
            <h2 class="text-2xl font-bold text-gray-200 mb-4">Cities in {{ $county->name }} County</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                @foreach($cities as $city)
                    <article class="bg-gray-700 p-3 md:px-4 md:pt-4 md:pb-5 text-gray-100 font-body rounded md:rounded-md shadow-lg">
                        <a href="{{ route('city.show', ['region' => $region, 'county' => $county, 'city' => $city]) }}" class="text-2xl hover:underline text-gray-200">
                            {{ $city->name }}
                        </a>

                        <div class="mt-4 text-gray-300 line-clamp-3">
                            {{ Str::limit($city->description, 150) }}
                        </div>

                        @if($city->content_count > 0)
                            <div class="mt-4 text-sm text-blue-400">
                                {{ $city->content_count }} {{ Str::plural('guide', $city->content_count) }}
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>

            {{-- County Featured Content --}}
            @if($featuredContent->isNotEmpty())
                <section class="mt-8 mb-8">
                    <h2 class="text-2xl font-bold text-gray-200 mb-4">Featured {{ $county->name }} County Guides</h2>
                    @foreach($featuredContent as $content)
                        <x-guide.card :content="$content" />
                    @endforeach
                    <div class="mt-4 text-right">
                        <a href="{{ route('guide.county', ['region' => $region, 'county' => $county]) }}" class="text-blue-400 hover:underline">View all {{ $county->name }} County guides →</a>
                    </div>
                </section>
            @endif

            {{-- City Content --}}
            @if(isset($cityContent) && $cityContent->isNotEmpty())
                <section class="mt-8">
                    <h2 class="text-2xl font-bold text-gray-200 mb-4">Latest From {{ $county->name }} County Cities</h2>
                    @foreach($cityContent as $content)
                        <x-guide.card :content="$content" />
                    @endforeach
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
