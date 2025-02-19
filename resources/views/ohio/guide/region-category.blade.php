{{-- resources/views/ohio/guide/region-category.blade.php --}}
<x-app-layout>
<x-slot name="title">{{ $category->name }} in {{ $region->name }}</x-slot>
<x-slot name="header">
    <h2 class="font-semibold text-3xl text-gray-200 dark:text-gray-200 leading-tight">
        {{ $category->name }} in {{ $region->name }}
    </h2>
</x-slot>

<div class="bg-gray-700 p-3 md:px-8 md:py-6 text-gray-100 rounded md:rounded-lg mb-8">
    <div class="flex items-center gap-2 text-sm text-gray-400 mb-4">
        <a href="{{ route('guide.region', $region) }}" class="hover:text-white">{{ $region->name }}</a>
        <span>&raquo;</span>
        <span class="font-medium text-white">{{ $category->name }}</span>
    </div>

    <h1 class="text-3xl font-bold mb-4">{{ $category->name }} in {{ $region->name }}</h1>
    <p class="text-xl text-gray-300">{{ $category->description }}</p>
</div>

@foreach($content as $item)
    <x-guide.card :content="$item" />
    @endforeach

    {{ $content->links() }}
    </x-app-layout>
