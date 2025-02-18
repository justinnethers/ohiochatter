{{-- resources/views/content/location.blade.php --}}
<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">{{ $location->name }} Guides</h1>
            <p class="mt-2 text-lg text-gray-600">{{ $location->description }}</p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($content as $item)
                <x-content.card :content="$item" />
            @endforeach
        </div>

        <div class="mt-8">
            {{ $content->links() }}
        </div>
    </div>
</x-app-layout>
