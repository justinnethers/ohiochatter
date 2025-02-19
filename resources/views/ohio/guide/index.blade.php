{{-- resources/views/ohio/guide/index.blade.php --}}
<x-app-layout>
    <x-slot name="title">Ohio Guide</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-3xl text-gray-200 dark:text-gray-200 leading-tight">
            Ohio Guide
        </h2>
    </x-slot>

    <x-slot name="headerAction">
        @auth
            <a href="{{ route('guide.create') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-400 hover:text-gray-300 hover:border-gray-700 transition duration-150 ease-in-out">
                Create Guide
            </a>
        @endauth
    </x-slot>

    <div>
        @if($categories->isNotEmpty())
            <div class="flex flex-wrap gap-2 mb-6">
                @foreach($categories as $category)
                    <a href="{{ route('guide.category', $category) }}"
                       class="inline-block px-4 py-2 rounded-full text-sm font-medium
                              text-gray-600 hover:text-gray-900 hover:bg-gray-100
                              dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-800">
                        {{ $category->name }}
                        <span class="text-gray-500">({{ $category->content_count }})</span>
                    </a>
                @endforeach
            </div>
        @endif

        @if($featuredContent->isNotEmpty())
            <section class="mb-8">
                <h3 class="text-xl font-semibold text-gray-200 mb-4">Featured Guides</h3>
                @foreach($featuredContent as $content)
                    <x-guide.card :content="$content" />
                @endforeach
            </section>
        @endif

        <section>
            <h3 class="text-xl font-semibold text-gray-200 mb-4">Recent Guides</h3>
            @foreach($recentContent as $content)
                <x-guide.card :content="$content" />
            @endforeach
        </section>

        {{ $recentContent->links() }}
    </div>
</x-app-layout>
