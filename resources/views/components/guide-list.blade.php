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
