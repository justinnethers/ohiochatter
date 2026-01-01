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
                </header>

                @if($content->excerpt)
                    <div class="text-lg text-steel-300 mb-6 border-l-4 border-accent-500 pl-4">
                        {{ $content->excerpt }}
                    </div>
                @endif

                {{-- Content Blocks --}}
                @if(!empty($content->blocks))
                    <div class="space-y-6">
                        @foreach($content->blocks as $block)
                            @switch($block['type'] ?? '')
                                @case('text')
                                    @if(!empty($block['data']['content']))
                                        <div class="prose prose-lg prose-invert max-w-none">
                                            {!! $block['data']['content'] !!}
                                        </div>
                                    @endif
                                    @break

                                @case('image')
                                    @if(!empty($block['data']['path']))
                                        <figure class="my-6">
                                            <img src="{{ Storage::url($block['data']['path']) }}"
                                                alt="{{ $block['data']['alt'] ?? '' }}"
                                                class="rounded-xl max-w-full h-auto">
                                            @if(!empty($block['data']['caption']))
                                                <figcaption class="mt-2 text-center text-sm text-steel-400">
                                                    {{ $block['data']['caption'] }}
                                                </figcaption>
                                            @endif
                                        </figure>
                                    @endif
                                    @break

                                @case('carousel')
                                    @if(!empty($block['data']['images']))
                                        <div class="my-6 overflow-x-auto">
                                            <div class="flex gap-4 pb-4">
                                                @foreach($block['data']['images'] as $image)
                                                    <img src="{{ Storage::url($image['path']) }}"
                                                        alt="{{ $image['alt'] ?? '' }}"
                                                        class="h-64 w-auto rounded-xl shrink-0">
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    @break

                                @case('video')
                                    @if(!empty($block['data']['url']))
                                        @php
                                            $videoUrl = $block['data']['url'];
                                            $embedUrl = null;

                                            // YouTube
                                            if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $videoUrl, $matches)) {
                                                $embedUrl = "https://www.youtube.com/embed/{$matches[1]}";
                                            }
                                            // Vimeo
                                            elseif (preg_match('/vimeo\.com\/(\d+)/', $videoUrl, $matches)) {
                                                $embedUrl = "https://player.vimeo.com/video/{$matches[1]}";
                                            }
                                        @endphp

                                        <div class="my-6">
                                            @if($embedUrl)
                                                <div class="aspect-video rounded-xl overflow-hidden">
                                                    <iframe src="{{ $embedUrl }}"
                                                        class="w-full h-full"
                                                        frameborder="0"
                                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                        allowfullscreen></iframe>
                                                </div>
                                            @else
                                                <a href="{{ $videoUrl }}" target="_blank" rel="noopener noreferrer"
                                                    class="flex items-center gap-2 text-accent-400 hover:text-accent-300">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    Watch Video
                                                </a>
                                            @endif
                                            @if(!empty($block['data']['caption']))
                                                <p class="mt-2 text-sm text-steel-400">{{ $block['data']['caption'] }}</p>
                                            @endif
                                        </div>
                                    @endif
                                    @break

                                @case('list')
                                    @if(!empty($block['data']['items']))
                                        <x-guide-list
                                            :items="collect($block['data']['items'])->map(fn($item) => [
                                                'title' => $item['title'] ?? '',
                                                'description' => $item['description'] ?? '',
                                                'image' => $item['image'] ?? null,
                                                'address' => $item['address'] ?? $item['website'] ?? '',
                                                'rating' => $item['rating'] ?? null,
                                            ])->toArray()"
                                            :settings="[
                                                'ranked' => $block['data']['ranked'] ?? true,
                                                'title' => $block['data']['title'] ?? null,
                                                'countdown' => $block['data']['countdown'] ?? false,
                                            ]"
                                        />
                                    @endif
                                    @break
                            @endswitch
                        @endforeach
                    </div>
                @endif

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
