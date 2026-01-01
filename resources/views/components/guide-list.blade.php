@props(['items' => [], 'settings' => []])

@php
    $isRanked = $settings['ranked'] ?? true;
    $listTitle = $settings['title'] ?? null;
    $isCountdown = $settings['countdown'] ?? false;
    $displayItems = $isCountdown ? array_reverse($items) : $items;
    $totalItems = count($items);
@endphp

@if(!empty($items))
    <div class="my-8">
        {{-- Optional List Title --}}
        @if($listTitle)
            <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-3">
                @if($isRanked)
                    <span class="w-1 h-8 bg-accent-500 rounded-full"></span>
                @endif
                {{ $listTitle }}
            </h2>
        @endif

        <div class="space-y-6">
            @foreach($displayItems as $index => $item)
                @php
                    $rankNumber = $isCountdown
                        ? $totalItems - $index
                        : $index + 1;
                @endphp
                <div class="bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl overflow-hidden border border-steel-700/50 shadow-lg shadow-black/20">
                    <div class="flex flex-col md:flex-row">
                        {{-- Image --}}
                        @if(!empty($item['image']))
                            <div class="md:w-48 md:shrink-0">
                                <img src="{{ Storage::url($item['image']) }}"
                                    alt="{{ $item['title'] }}"
                                    class="w-full h-48 md:h-full object-cover">
                            </div>
                        @endif

                        {{-- Content --}}
                        <div class="flex-1 p-5">
                            <div class="flex items-start gap-4">
                                {{-- Rank Number --}}
                                @if($isRanked)
                                    <div class="shrink-0 flex items-center justify-center w-10 h-10 rounded-full bg-accent-500/20 border border-accent-500/30">
                                        <span class="text-accent-400 font-bold text-lg">#{{ $rankNumber }}</span>
                                    </div>
                                @endif

                                <div class="flex-1 min-w-0">
                                    {{-- Title & Rating --}}
                                    <div class="flex items-start justify-between gap-3 mb-2">
                                        <h3 class="text-xl font-bold text-white">{{ $item['title'] }}</h3>

                                        @if(!empty($item['rating']))
                                            <div class="shrink-0 flex items-center gap-1">
                                                @for($star = 1; $star <= 5; $star++)
                                                    <span class="text-lg {{ $item['rating'] >= $star ? 'text-amber-400' : 'text-steel-600' }}">★</span>
                                                @endfor
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Description --}}
                                    <p class="text-steel-300 leading-relaxed">{{ $item['description'] }}</p>

                                    {{-- Nested Blocks --}}
                                    @if(!empty($item['blocks']))
                                        <div class="mt-4 space-y-4">
                                            @foreach($item['blocks'] as $nestedBlock)
                                                @switch($nestedBlock['type'])
                                                    @case('text')
                                                        @if(!empty($nestedBlock['data']['content']))
                                                            <div class="prose prose-sm prose-invert max-w-none text-steel-300">
                                                                {!! $nestedBlock['data']['content'] !!}
                                                            </div>
                                                        @endif
                                                        @break
                                                    @case('image')
                                                        @if(!empty($nestedBlock['data']['url']) || !empty($nestedBlock['data']['path']))
                                                            <figure>
                                                                <img src="{{ $nestedBlock['data']['url'] ?? Storage::url($nestedBlock['data']['path']) }}"
                                                                    alt="{{ $nestedBlock['data']['caption'] ?? '' }}"
                                                                    class="rounded-lg max-w-full h-auto">
                                                                @if(!empty($nestedBlock['data']['caption']))
                                                                    <figcaption class="mt-2 text-center text-sm text-steel-400">
                                                                        {{ $nestedBlock['data']['caption'] }}
                                                                    </figcaption>
                                                                @endif
                                                            </figure>
                                                        @endif
                                                        @break
                                                    @case('video')
                                                        @if(!empty($nestedBlock['data']['url']))
                                                            <div class="aspect-video rounded-lg overflow-hidden bg-steel-900">
                                                                @php
                                                                    $videoUrl = $nestedBlock['data']['url'];
                                                                    $youtubeId = null;
                                                                    if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $videoUrl, $matches)) {
                                                                        $youtubeId = $matches[1];
                                                                    }
                                                                @endphp
                                                                @if($youtubeId)
                                                                    <iframe src="https://www.youtube.com/embed/{{ $youtubeId }}"
                                                                        class="w-full h-full" frameborder="0"
                                                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                                        allowfullscreen></iframe>
                                                                @else
                                                                    <a href="{{ $videoUrl }}" target="_blank" class="flex items-center justify-center h-full text-accent-400 hover:text-accent-300">
                                                                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                        </svg>
                                                                    </a>
                                                                @endif
                                                            </div>
                                                            @if(!empty($nestedBlock['data']['caption']))
                                                                <p class="mt-2 text-center text-sm text-steel-400">{{ $nestedBlock['data']['caption'] }}</p>
                                                            @endif
                                                        @endif
                                                        @break
                                                    @case('carousel')
                                                        @if(!empty($nestedBlock['data']['images']) || !empty($nestedBlock['data']['urls']))
                                                            <div class="overflow-x-auto">
                                                                <div class="flex gap-3 pb-2">
                                                                    @if(!empty($nestedBlock['data']['images']))
                                                                        @foreach($nestedBlock['data']['images'] as $image)
                                                                            <img src="{{ Storage::url($image['path']) }}"
                                                                                alt="{{ $image['alt'] ?? '' }}"
                                                                                class="h-40 w-auto rounded-lg shrink-0">
                                                                        @endforeach
                                                                    @elseif(!empty($nestedBlock['data']['urls']))
                                                                        @foreach(explode(',', $nestedBlock['data']['urls']) as $url)
                                                                            <img src="{{ trim($url) }}"
                                                                                alt=""
                                                                                class="h-40 w-auto rounded-lg shrink-0">
                                                                        @endforeach
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endif
                                                        @break
                                                    @case('list')
                                                        @if(!empty($nestedBlock['data']['items']))
                                                            <div class="pl-4 border-l-2 border-steel-600/50 space-y-2">
                                                                @if(!empty($nestedBlock['data']['title']))
                                                                    <h4 class="font-semibold text-white text-sm">{{ $nestedBlock['data']['title'] }}</h4>
                                                                @endif
                                                                @foreach($nestedBlock['data']['items'] as $nestedItemIndex => $nestedListItem)
                                                                    <div class="flex gap-2">
                                                                        @if($nestedBlock['data']['ranked'] ?? true)
                                                                            <span class="shrink-0 w-5 h-5 rounded-full bg-green-500/20 text-green-400 text-xs font-bold flex items-center justify-center">
                                                                                {{ $nestedItemIndex + 1 }}
                                                                            </span>
                                                                        @endif
                                                                        <div>
                                                                            <p class="font-medium text-white text-sm">{{ $nestedListItem['title'] ?? '' }}</p>
                                                                            @if(!empty($nestedListItem['description']))
                                                                                <p class="text-steel-400 text-sm">{{ $nestedListItem['description'] }}</p>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                        @break
                                                @endswitch
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- Address/Link --}}
                                    @if(!empty($item['address']))
                                        <div class="mt-3 flex items-center gap-2 text-sm">
                                            @if(filter_var($item['address'], FILTER_VALIDATE_URL))
                                                <a href="{{ $item['address'] }}" target="_blank" rel="noopener noreferrer"
                                                    class="inline-flex items-center gap-1.5 text-accent-400 hover:text-accent-300 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                                    </svg>
                                                    Visit Website
                                                </a>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 text-steel-400">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    </svg>
                                                    {{ $item['address'] }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
