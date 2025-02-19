{{-- resources/views/ohio/regions/show.blade.php --}}
<x-app-layout>
    <x-slot name="title">{{ $region->name }}</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-3xl text-gray-200 dark:text-gray-200 leading-tight">
            {{ $region->name }}
        </h2>
    </x-slot>
    <div>
        <div class="md:rounded-lg md:bg-gray-800 p-2 md:p-8 md:pt-4 md:mt-4">
            <div class="bg-gray-700 rounded-lg p-6 mb-8">
                <h1 class="text-3xl font-bold text-gray-100 mb-4">{{ $region->name }}</h1>
                <p class="text-gray-300 text-lg">{{ $region->description }}</p>
            </div>

            {{-- Category filters --}}
            @if($categories->isNotEmpty())
                <div class="flex flex-wrap gap-2 mb-6">
                    <h3 class="text-xl font-semibold text-gray-200 mr-4 flex items-center">Browse by category:</h3>
                    @foreach($categories as $category)
                        <a href="{{ route('guide.region.category', ['region' => $region, 'category' => $category]) }}"
                           class="inline-block px-4 py-2 rounded-full text-sm font-medium
                                  text-gray-400 hover:text-white hover:bg-gray-700
                                  transition duration-150 ease-in-out">
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- Counties --}}
            <h2 class="text-2xl font-bold text-gray-200 mb-4">Counties in {{ $region->name }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                @foreach($counties as $county)
                    <article class="bg-gray-700 p-3 md:px-4 md:pt-4 md:pb-5 text-gray-100 font-body rounded md:rounded-md shadow-lg">
                        <a href="{{ route('county.show', ['region' => $region, 'county' => $county]) }}" class="text-2xl hover:underline text-gray-200">
                            {{ $county->name }} County
                        </a>

                        <div class="mt-4 text-gray-300 line-clamp-3">
                            {{ Str::limit($county->description, 150) }}
                        </div>

                        @if($county->content_count > 0)
                            <div class="mt-4 text-sm text-blue-400">
                                {{ $county->content_count }} {{ Str::plural('guide', $county->content_count) }}
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>

            {{-- Regional Featured Content --}}
            @if($featuredContent->isNotEmpty())
                <section class="mt-8 mb-8">
                    <h2 class="text-2xl font-bold text-gray-200 mb-4">Featured {{ $region->name }} Guides</h2>
                    @foreach($featuredContent as $content)
                        <x-guide.card :content="$content" />
                    @endforeach
                    <div class="mt-4 text-right">
                        <a href="{{ route('guide.region', $region) }}" class="text-blue-400 hover:underline">View all {{ $region->name }} guides →</a>
                    </div>
                </section>
            @endif

            {{-- County Content --}}
            @if(isset($countyContent) && $countyContent->isNotEmpty())
                <section class="mt-8">
                    <h2 class="text-2xl font-bold text-gray-200 mb-4">Latest From {{ $region->name }} Counties</h2>
                    @foreach($countyContent as $content)
                        <x-guide.card :content="$content" />
                    @endforeach
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
