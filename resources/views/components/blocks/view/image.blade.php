@props(['block', 'index' => 0, 'path' => '', 'nested' => false])

@php
    $src = $block['data']['url'] ?? ($block['data']['path'] ? Storage::url($block['data']['path']) : null);
@endphp

@if($src)
    <figure class="{{ $nested ? '' : 'my-6' }}">
        <img src="{{ $src }}"
            alt="{{ $block['data']['alt'] ?? $block['data']['caption'] ?? '' }}"
            class="rounded-lg max-w-full h-auto">
        @if(!empty($block['data']['caption']))
            <figcaption class="mt-2 text-center text-sm text-steel-400">
                {{ $block['data']['caption'] }}
            </figcaption>
        @endif
    </figure>
@endif
