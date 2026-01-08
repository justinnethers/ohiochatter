{{-- resources/views/content/show.blade.php --}}
<x-app-layout :seo="$seo ?? null">
    <article class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Content Header --}}
        <header class="mb-8">
            @if($content->contentCategory)
                <a href="{{ route('content.category', $content->contentCategory) }}"
                   class="text-blue-600 hover:text-blue-800">
                    {{ $content->contentCategory->name }}
                </a>
            @endif
            <h1 class="text-4xl font-bold text-gray-900 mt-2 mb-4">{{ $content->title }}</h1>
            <div class="flex items-center text-gray-600">
                <span>By {{ $content->author->username }}</span>
                <span class="mx-2">·</span>
                <span>{{ $content->published_at->format('F j, Y') }}</span>
            </div>
        </header>

        {{-- Content Body --}}
        <div class="prose prose-lg max-w-none">
            {!! Str::markdown($content->body) !!}
        </div>

        {{-- List Items (if present) --}}
        @if(!empty($content->metadata['list_items']))
            <x-guide-list
                :items="$content->metadata['list_items']"
                :settings="$content->metadata['list_settings'] ?? []"
            />
        @endif

        {{-- Related Content --}}
        @if($relatedContent->isNotEmpty())
            <section class="mt-12 pt-8 border-t">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Related Guides</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach($relatedContent as $related)
                        <x-guide.card :content="$related" />
                    @endforeach
                </div>
            </section>
        @endif
    </article>
</x-app-layout>
