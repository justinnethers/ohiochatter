@props(['block', 'index' => 0, 'path' => '', 'nested' => false])

@php
    $items = $block['data']['items'] ?? [];
@endphp

@if(!empty($items))
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
            'nested' => $nested,
        ]"
    />
@endif
