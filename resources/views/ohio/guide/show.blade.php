{{-- resources/views/ohio/guide/show.blade.php --}}
<x-app-layout>
    <x-slot name="title">{{ $content->title }}</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-3xl text-gray-200 dark:text-gray-200 leading-tight">
            {{ $content->title }}
        </h2>
    </x-slot>

    <div>
        <div class="md:rounded-lg p-2 md:p-8 md:pt-4 md:mt-4">
            <article class="bg-gray-800 p-3 md:px-8 md:py-6 text-gray-100 rounded md:rounded-lg">
                <header class="mb-6">
                    <div class="flex flex-wrap gap-2 mb-3">
                        @php
                            $location = null;
                            if ($content->locatable_type === 'App\\Models\\Region' && $content->locatable) {
                                $location = [
                                    'name' => $content->locatable->name,
                                    'url' => route('guide.region', $content->locatable)
                                ];
                            } elseif ($content->locatable_type === 'App\\Models\\County' && $content->locatable) {
                                $location = [
                                    'name' => $content->locatable->name . ' County',
                                    'url' => route('guide.county', ['region' => $content->locatable->region, 'county' => $content->locatable])
                                ];
                            } elseif ($content->locatable_type === 'App\\Models\\City' && $content->locatable) {
                                $location = [
                                    'name' => $content->locatable->name,
                                    'url' => route('guide.city', [
                                        'region' => $content->locatable->county->region,
                                        'county' => $content->locatable->county,
                                        'city' => $content->locatable
                                    ])
                                ];
                            }
                        @endphp

                        @if($location)
                            <a href="{{ $location['url'] }}" class="text-sm font-medium px-3 py-1 rounded-full bg-blue-500 text-white hover:bg-blue-600">
                                {{ $location['name'] }}
                            </a>
                        @endif

                        @if($content->contentCategory)
                            <a href="{{ route('guide.category', $content->contentCategory) }}" class="text-sm font-medium px-3 py-1 rounded-full bg-gray-600 text-white hover:bg-gray-700">
                                {{ $content->contentCategory->name }}
                            </a>
                        @endif
                    </div>

                    <h1 class="text-3xl md:text-4xl font-bold text-white mb-4">{{ $content->title }}</h1>

                    <div class="flex items-center text-gray-400 text-sm">
                        @if($content->author)
                            <div class="flex items-center">
                                <x-avatar :avatar-path="$content->author->avatar_path" size="8" />
                                <span class="ml-2">By <a href="{{ route('profile.show', $content->author) }}" class="text-blue-400 hover:underline">{{ $content->author->username }}</a></span>
                            </div>
                            <span class="mx-2">&bull;</span>
                        @endif
                        <span>{{ $content->created_at->format('F j, Y') }}</span>
                        @if($content->updated_at)
                            <span class="mx-2">&bull;</span>
                            <span>Updated {{ $content->updated_at->format('F j, Y') }}</span>
                        @endif
                    </div>
                </header>

                @if($content->excerpt)
                    <div class="text-xl text-gray-300 mb-6">
                        {{ $content->excerpt }}
                    </div>
                @endif

                <div class="prose prose-lg prose-invert max-w-none">
                    {!! Str::markdown($content->body) !!}
                </div>

                @if($content->metadata)
                    <div class="mt-8 pt-8 border-t border-gray-600">
                        @if($content->contentType && $content->contentType->slug === 'list' && isset($content->metadata['items']))
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                @foreach($content->metadata['items'] as $item)
                                    <div class="bg-gray-800 rounded-lg p-4">
                                        <h3 class="text-lg font-semibold mb-2">{{ $item['name'] ?? '' }}</h3>
                                        <p class="text-gray-300">{{ $item['description'] ?? '' }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            </article>

            @if(isset($relatedContent) && $relatedContent->isNotEmpty())
                <section class="mt-8">
                    <h2 class="text-2xl font-bold text-gray-200 mb-4">Related Guides</h2>
                    @foreach($relatedContent as $related)
                        <x-guide.card :content="$related" />
                    @endforeach
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
