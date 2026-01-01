@php
    $previewLocation = $this->getPreviewLocation();
    $previewCategories = $this->getPreviewCategories();
    $previewListItems = $this->getPreviewListItems();
@endphp

<div class="bg-gradient-to-br from-steel-800 to-steel-850 p-4 md:p-8 text-steel-100 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50">
    {{-- Preview Header Badge --}}
    <div class="mb-6 flex items-center justify-between">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-amber-500/20 text-amber-400 rounded-full text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            Preview Mode
        </div>
        <button type="button" wire:click="togglePreview"
            class="inline-flex items-center gap-2 px-3 py-1.5 bg-steel-700 text-steel-200 rounded-lg hover:bg-steel-600 transition-colors text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Back to Editor
        </button>
    </div>

    <article>
        <header class="mb-6">
            {{-- Location & Categories --}}
            <div class="flex flex-wrap gap-2 mb-4">
                @if($previewLocation)
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-accent-500/20 text-accent-400">
                        {{ $previewLocation['name'] }}
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-steel-700/50 text-steel-500 border border-dashed border-steel-600">
                        No location selected
                    </span>
                @endif

                @forelse($previewCategories as $category)
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-steel-700 text-steel-300">
                        {{ $category['name'] }}
                    </span>
                @empty
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-steel-700/50 text-steel-500 border border-dashed border-steel-600">
                        No categories selected
                    </span>
                @endforelse
            </div>

            {{-- Title --}}
            @if($title)
                <h1 class="text-2xl md:text-4xl font-bold text-white mb-4">{{ $title }}</h1>
            @else
                <div class="text-2xl md:text-4xl font-bold text-steel-600 mb-4 border-b-2 border-dashed border-steel-700 pb-2">
                    [Title will appear here]
                </div>
            @endif

            {{-- Author Info --}}
            <div class="flex items-center text-steel-400 text-sm">
                <div class="flex items-center">
                    <x-avatar :avatar-path="auth()->user()->avatar_path" size="8" />
                    <span class="ml-2">By <span class="text-accent-400">{{ auth()->user()->username }}</span></span>
                </div>
                <span class="mx-2">&bull;</span>
                <span>{{ now()->format('F j, Y') }}</span>
            </div>
        </header>

        {{-- Excerpt --}}
        @if($excerpt)
            <div class="text-lg text-steel-300 mb-6 border-l-4 border-accent-500 pl-4">
                {{ $excerpt }}
            </div>
        @endif

        {{-- Guide Metadata --}}
        @if($guideRating || $guideWebsite || $guideAddress)
            <div class="mb-6 p-4 bg-steel-900/50 rounded-lg border border-steel-700/50">
                <div class="flex flex-wrap gap-4">
                    @if($guideRating)
                        <div class="flex items-center gap-2">
                            <span class="text-steel-400 text-sm">Rating:</span>
                            <div class="flex items-center gap-0.5">
                                @for($star = 1; $star <= 5; $star++)
                                    <span class="text-lg {{ $guideRating >= $star ? 'text-amber-400' : 'text-steel-600' }}">★</span>
                                @endfor
                            </div>
                        </div>
                    @endif
                    @if($guideWebsite)
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-steel-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            <a href="{{ $guideWebsite }}" target="_blank" class="text-accent-400 hover:text-accent-300 text-sm">{{ $guideWebsite }}</a>
                        </div>
                    @endif
                    @if($guideAddress)
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-steel-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span class="text-steel-300 text-sm">{{ $guideAddress }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Body Content --}}
        @if($body)
            <div class="prose prose-lg prose-invert max-w-none mb-8">
                {!! Str::markdown($body) !!}
            </div>
        @else
            <div class="text-steel-500 italic mb-8 p-6 border-2 border-dashed border-steel-700 rounded-lg text-center">
                [Guide content will appear here]
            </div>
        @endif

        {{-- Content Blocks Preview --}}
        @if(!empty($blocks))
            <div class="space-y-6 mb-8">
                @foreach($blocks as $block)
                    @switch($block['type'])
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
                                <div class="my-6">
                                    <div class="p-4 bg-steel-900/50 rounded-lg border border-steel-700/50">
                                        <div class="flex items-center gap-2 text-steel-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span class="text-sm">Video: {{ $block['data']['url'] }}</span>
                                        </div>
                                        @if(!empty($block['data']['caption']))
                                            <p class="mt-2 text-sm text-steel-500">{{ $block['data']['caption'] }}</p>
                                        @endif
                                    </div>
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

        {{-- Legacy List Items --}}
        @if(!empty($previewListItems))
            <x-guide-list
                :items="$previewListItems"
                :settings="[
                    'ranked' => $listIsRanked,
                    'title' => $listTitle ?: null,
                    'countdown' => $listCountdown,
                ]"
            />
        @endif
    </article>
</div>
