@props(['block', 'index' => 0, 'path' => '', 'nested' => false])

@php
    $items = $block['data']['items'] ?? [];
@endphp

@if(!empty($items) && !$nested)
    {{-- Full list rendering using guide-list component --}}
    <x-guide-list
        :items="collect($items)->map(fn($item) => [
            'title' => $item['title'] ?? '',
            'description' => $item['description'] ?? '',
            'image' => $item['image'] ?? null,
            'address' => $item['address'] ?? $item['website'] ?? '',
            'rating' => $item['rating'] ?? null,
            'blocks' => $item['blocks'] ?? [],
        ])->toArray()"
        :settings="[
            'ranked' => $block['data']['ranked'] ?? true,
            'title' => $block['data']['title'] ?? null,
            'countdown' => $block['data']['countdown'] ?? false,
        ]"
    />
@elseif(!empty($items) && $nested)
    {{-- Simplified nested list rendering --}}
    <div class="pl-4 border-l-2 border-steel-600/50 space-y-2">
        @if(!empty($block['data']['title']))
            <h4 class="font-semibold text-white text-sm">{{ $block['data']['title'] }}</h4>
        @endif
        @php
            $isRanked = $block['data']['ranked'] ?? true;
        @endphp
        @foreach($items as $itemIndex => $item)
            <div class="flex gap-2">
                @if($isRanked)
                    <span class="shrink-0 w-5 h-5 rounded-full bg-green-500/20 text-green-400 text-xs font-bold flex items-center justify-center">
                        {{ $itemIndex + 1 }}
                    </span>
                @endif
                <div>
                    <p class="font-medium text-white text-sm">{{ $item['title'] ?? '' }}</p>
                    @if(!empty($item['description']))
                        <p class="text-steel-400 text-sm">{{ $item['description'] }}</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
