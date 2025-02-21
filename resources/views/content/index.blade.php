{{-- resources/views/content/index.blade.php --}}
<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Featured Content --}}
        <section class="mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Featured Guides</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($featuredContent as $content)
                    <x-guide.card :content="$content" />
                @endforeach
            </div>
        </section>

        {{-- Recent Content --}}
        <section>
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Recent Guides</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($recentContent as $content)
                    <x-guide.card :content="$content" />
                @endforeach
            </div>
        </section>
    </div>
</x-app-layout>
