{{-- resources/views/ohio/guide/county-category.blade.php --}}
<x-app-layout :seo="$seo ?? null">
    <x-slot name="title">{{ $category->name }} in {{ $county->name }}</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-white leading-tight flex items-center gap-3">
                <span class="hidden md:inline-block w-1 h-6 bg-accent-500 rounded-full"></span>
                {{ $category->name }} in {{ $county->name }}
            </h2>
        </div>
    </x-slot>

    <div class="container mx-auto">
        <div class="md:rounded-2xl md:bg-gradient-to-br md:from-steel-800/50 md:to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-2 md:p-8 md:mt-4">
            <x-breadcrumbs :items="[
                ['title' => 'Ohio Guide', 'url' => route('guide.index')],
                ['title' => $region->name, 'url' => route('guide.region', $region)],
                ['title' => $county->name . ' County', 'url' => route('guide.county', ['region' => $region, 'county' => $county])],
                ['title' => $category->name],
            ]"/>

            <div class="bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl p-6 mb-6 shadow-lg shadow-black/20 border border-steel-700/50">
                <h1 class="text-2xl md:text-3xl font-bold text-white mb-3">{{ $category->name }} in {{ $county->name }} County</h1>
                @if($category->description)
                    <p class="text-steel-300 text-lg">{{ $category->description }}</p>
                @endif
            </div>

            @forelse($content as $item)
                <x-guide.card :content="$item" />
            @empty
                <div class="bg-gradient-to-br from-steel-800 to-steel-850 p-6 text-steel-300 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50 text-center">
                    No guides in this category for {{ $county->name }} County yet.
                </div>
            @endforelse

            <div class="mt-6">{{ $content->links() }}</div>
        </div>
    </div>
</x-app-layout>
