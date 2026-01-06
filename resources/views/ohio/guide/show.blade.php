{{-- resources/views/ohio/guide/show.blade.php --}}
<x-app-layout>
    <x-slot name="title">{{ $content->title }}</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-white leading-tight flex items-center gap-3">
                <span class="hidden md:inline-block w-1 h-6 bg-accent-500 rounded-full"></span>
                {{ Str::limit($content->title, 50) }}
            </h2>
        </div>
    </x-slot>

    <div class="container mx-auto">
        <div class="md:rounded-2xl md:bg-gradient-to-br md:from-steel-800/50 md:to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-2 md:p-8 md:mt-4">
            <article class="bg-gradient-to-br from-steel-800 to-steel-850 p-4 md:p-8 text-steel-100 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50">
                <header class="mb-6">
                    <div class="flex flex-wrap gap-2 mb-4">
                        @php
                            $location = null;
                            if ($content->locatable_type === 'App\\Models\\Region' && $content->locatable) {
                                $location = ['name' => $content->locatable->name, 'url' => route('guide.region', $content->locatable)];
                            } elseif ($content->locatable_type === 'App\\Models\\County' && $content->locatable) {
                                $location = ['name' => $content->locatable->name . ' County', 'url' => route('guide.county', ['region' => $content->locatable->region, 'county' => $content->locatable])];
                            } elseif ($content->locatable_type === 'App\\Models\\City' && $content->locatable) {
                                $location = ['name' => $content->locatable->name, 'url' => route('guide.city', ['region' => $content->locatable->county->region, 'county' => $content->locatable->county, 'city' => $content->locatable])];
                            }
                        @endphp

                        @if($location)
                            <a href="{{ $location['url'] }}" class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-accent-500/20 text-accent-400 hover:bg-accent-500/30 transition-colors">
                                {{ $location['name'] }}
                            </a>
                        @endif

                        @foreach($content->contentCategories as $category)
                            <a href="{{ route('guide.category', $category) }}" class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-steel-700 text-steel-300 hover:bg-steel-600 hover:text-white transition-colors">
                                {{ $category->name }}
                            </a>
                        @endforeach
                    </div>

                    <h1 class="text-2xl md:text-4xl font-bold text-white mb-4">{{ $content->title }}</h1>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center text-steel-400 text-sm">
                            @if($content->author)
                                <div class="flex items-center">
                                    <x-avatar :avatar-path="$content->author->avatar_path" size="8" />
                                    <span class="ml-2">By <a href="{{ route('profile.show', $content->author) }}" class="text-accent-400 hover:text-accent-300 transition-colors">{{ $content->author->username }}</a></span>
                                </div>
                                <span class="mx-2">&bull;</span>
                            @endif
                            <span>{{ $content->created_at->format('F j, Y') }}</span>
                            @if($content->updated_at && $content->updated_at->ne($content->created_at))
                                <span class="mx-2">&bull;</span>
                                <span>Updated {{ $content->updated_at->format('F j, Y') }}</span>
                            @endif
                        </div>

                        @can('update', $content)
                            <a href="{{ route('guide.edit-content', $content) }}"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-steel-700 text-steel-200 rounded-lg hover:bg-steel-600 transition-colors text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit Guide
                            </a>
                        @endcan
                    </div>
                </header>

                @if($content->excerpt)
                    <div class="text-lg text-steel-300 mb-6 border-l-4 border-accent-500 pl-4">
                        {{ $content->excerpt }}
                    </div>
                @endif

                {{-- Content Blocks --}}
                <x-blocks.renderer :blocks="$content->blocks ?? []" mode="view" />

            </article>

            @if(isset($relatedContent) && $relatedContent->isNotEmpty())
                <section class="mt-8">
                    <h2 class="text-lg font-semibold text-white mb-4">Related Guides</h2>
                    @foreach($relatedContent as $related)
                        <x-guide.card :content="$related" />
                    @endforeach
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
