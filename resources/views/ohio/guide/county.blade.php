{{-- resources/views/ohio/guide/county.blade.php --}}
<x-app-layout>
    <x-slot name="title">{{ $county->name }} Guides</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-3xl text-gray-200 dark:text-gray-200 leading-tight">
            {{ $county->name }} Guides
        </h2>
    </x-slot>

    <div>
        <div class="md:rounded-lg md:bg-gray-800 p-2 md:p-8 md:pt-4 md:mt-4">
            <div class="bg-gray-700 rounded-lg p-6 mb-8">
                <div class="flex items-center gap-2 text-sm text-gray-400 mb-4">
                    <a href="{{ route('guide.region', $region) }}" class="hover:text-white">{{ $region->name }}</a>
                    <span>&raquo;</span>
                    <span class="font-medium text-white">{{ $county->name }}</span>
                </div>

                <h1 class="text-3xl font-bold text-gray-100 mb-4">{{ $county->name }} County Guides</h1>
                <p class="text-gray-300 text-lg">{{ $county->description }}</p>
            </div>

            @if($categories->isNotEmpty())
                <div class="flex flex-wrap gap-2 mb-6">
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

            @foreach($content as $item)
                <x-guide.card :content="$item" />
            @endforeach

            {{ $content->links() }}

            {{-- City Content --}}
            @if(isset($cityContent) && $cityContent->isNotEmpty())
                <section class="mt-8">
                    <h2 class="text-2xl font-bold text-gray-200 mb-4">Latest From {{ $county->name }} County Cities</h2>
                    @foreach($cityContent as $item)
                        <x-guide.card :content="$item" />
                    @endforeach
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
