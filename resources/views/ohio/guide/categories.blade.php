<x-app-layout>
    <x-slot name="title">Guide Categories</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-3xl text-gray-200 dark:text-gray-200 leading-tight">
            Guide Categories
        </h2>
    </x-slot>
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Guide Categories</h1>
        <p class="text-xl text-gray-600">Browse all content categories in our Ohio guide</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($categories as $category)
            <div class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                <h3 class="text-xl font-semibold text-gray-900 mb-3">
                    <a href="{{ route('guide.category', $category->slug) }}" class="hover:text-blue-600">
                        {{ $category->name }}
                    </a>
                </h3>
                
                @if($category->description)
                    <p class="text-gray-600 mb-4">{{ $category->description }}</p>
                @endif
                
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">
                        {{ $category->content_count }} {{ Str::plural('article', $category->content_count) }}
                    </span>
                    <a href="{{ route('guide.category', $category->slug) }}" 
                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        View Articles →
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <p class="text-gray-500 text-lg">No categories available yet.</p>
            </div>
        @endforelse
    </div>
</div>
</x-app-layout>