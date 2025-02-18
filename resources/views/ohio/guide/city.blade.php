<x-app-layout>
    <x-slot name="title">{{ $city->name }} Guides</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-3xl text-gray-200 dark:text-gray-200 leading-tight">
            {{ $city->name }} Guides
        </h2>
    </x-slot>

    <div>
        <div class="md:rounded-lg md:bg-gray-800 p-2 md:p-8 md:pt-4 md:mt-4">
            <div class="bg-gray-700 rounded-lg p-6 mb-8">
                <div class="flex items-center gap-2 text-sm text-gray-400 mb-4">
                    <a href="{{ route('guide.region', $region) }}" class="hover:text-white">{{ $region->name }}</a>
                    <span>&raquo;</span>
                    <a href="{{ route('guide.county', ['region' => $region, 'county' => $county]) }}" class="hover:text-white">{{ $county->name }} County</a>
                    <span>&raquo;</span>
                    <span class="font-medium text-white">{{ $city->name }}</span>
                </div>

                <h1 class="text-3xl font-bold text-gray-100 mb-4">{{ $city->name }} Guides</h1>
                <p class="text-gray-300 text-lg">{{ $city->description }}</p>
            </div>

            @if($categories->isNotEmpty())
                <div class="flex flex-wrap gap-2 mb-6">
                    @foreach($categories as $category)
                        <a href="{{ route('guide.city.category', ['region' => $region, 'county' => $county, 'city' => $city, 'category' => $category]) }}"
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
        </div>
    </div>
</x-app-layout>
