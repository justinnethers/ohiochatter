{{-- resources/views/ohio/guide/categories.blade.php --}}
<x-app-layout :seo="$seo ?? null">
    <x-slot name="title">Guide Categories</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-white leading-tight flex items-center gap-3">
                <span class="hidden md:inline-block w-1 h-6 bg-accent-500 rounded-full"></span>
                Guide Categories
            </h2>
        </div>
    </x-slot>

    <div class="container mx-auto">
        <div class="md:rounded-2xl md:bg-gradient-to-br md:from-steel-800/50 md:to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-2 md:p-8 md:mt-4">
            <x-breadcrumbs :items="[
                ['title' => 'Ohio Guide', 'url' => route('guide.index')],
                ['title' => 'Categories'],
            ]"/>

            <div class="bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl p-6 mb-6 shadow-lg shadow-black/20 border border-steel-700/50">
                <h1 class="text-2xl md:text-3xl font-bold text-white mb-3">Guide Categories</h1>
                <p class="text-steel-300 text-lg">Browse all content categories in our Ohio guide</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($categories as $category)
                    <a href="{{ route('guide.category', $category->slug) }}" class="group bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl p-6 shadow-lg shadow-black/20 border border-steel-700/50 hover:border-steel-600 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 relative overflow-hidden">
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-accent-400 to-accent-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                        <h3 class="text-lg font-semibold text-white mb-2 group-hover:text-accent-400 transition-colors">
                            {{ $category->name }}
                        </h3>

                        @if($category->description)
                            <p class="text-steel-400 text-sm mb-4">{{ $category->description }}</p>
                        @endif

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-steel-500">
                                {{ $category->content_count }} {{ Str::plural('article', $category->content_count) }}
                            </span>
                            <span class="text-accent-400 text-sm font-medium group-hover:text-accent-300 transition-colors">
                                View &rarr;
                            </span>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full bg-gradient-to-br from-steel-800 to-steel-850 p-6 text-steel-300 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50 text-center">
                        No categories available yet.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
