<x-app-layout>
    <x-slot name="title">{{ $region->name }} Guides</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-3xl text-gray-200 dark:text-gray-200 leading-tight">
            {{ $region->name }} Guides
        </h2>
    </x-slot>

    <div>
        <div class="md:rounded-lg md:bg-gray-800 p-2 md:p-8 md:pt-4 md:mt-4">
            <div class="bg-gray-700 rounded-lg p-6 mb-8">
                <h1 class="text-3xl font-bold text-gray-100 mb-4">{{ $region->name }} Guides</h1>
                <p class="text-gray-300 text-lg">{{ $region->description }}</p>
            </div>

            @if($categories->isNotEmpty())
                <div class="flex flex-wrap gap-2 mb-6">
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

            @if($content->count() > 0)
                @foreach($content as $item)
                    <x-guide.card :content="$item" />
                @endforeach

                {{ $content->links() }}
            @else
                <div class="text-center py-8">
                    <p class="text-gray-400">No guides available for this region yet.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
