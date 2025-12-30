{{-- resources/views/ohio/guide/city.blade.php --}}
<x-app-layout>
    <x-slot name="title">{{ $city->name }} Guides</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-white leading-tight flex items-center gap-3">
                <span class="hidden md:inline-block w-1 h-6 bg-accent-500 rounded-full"></span>
                {{ $city->name }} Guides
            </h2>
        </div>
    </x-slot>

    <div class="container mx-auto">
        <div class="md:rounded-2xl md:bg-gradient-to-br md:from-steel-800/50 md:to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-2 md:p-8 md:mt-4">
            <x-breadcrumbs :items="[
                ['title' => 'Ohio Guide', 'url' => route('guide.index')],
                ['title' => $region->name, 'url' => route('guide.region', $region)],
                ['title' => $county->name . ' County', 'url' => route('guide.county', ['region' => $region, 'county' => $county])],
                ['title' => $city->name],
            ]"/>

            <div class="bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl p-6 mb-6 shadow-lg shadow-black/20 border border-steel-700/50">
                <h1 class="text-2xl md:text-3xl font-bold text-white mb-3">{{ $city->name }} Guides</h1>
                @if($city->description)
                    <p class="text-steel-300 text-lg">{{ $city->description }}</p>
                @endif
            </div>

            @if($categories->isNotEmpty())
                <div class="flex flex-wrap gap-2 mb-6">
                    @foreach($categories as $category)
                        <a href="{{ route('guide.city.category', ['region' => $region, 'county' => $county, 'city' => $city, 'category' => $category]) }}"
                           class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium text-steel-300 hover:text-white hover:bg-steel-700/50 border border-steel-700/50 hover:border-steel-600 transition-all duration-200">
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            @endif

            @forelse($content as $item)
                <x-guide.card :content="$item" />
            @empty
                <div class="bg-gradient-to-br from-steel-800 to-steel-850 p-6 text-steel-300 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50 text-center">
                    No guides available for this city yet.
                </div>
            @endforelse

            <div class="mt-6">{{ $content->links() }}</div>
        </div>
    </div>
</x-app-layout>
