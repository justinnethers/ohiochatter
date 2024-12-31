@props(['thread'])
@php
    $forum = Cache::remember("forum_{$thread->forum->id}", 3600, fn() => $thread->forum);
@endphp
<div class="flex order-0 flex-1 md:flex-none">
    <a class="flex items-center justify-items bg-{{ $forum->color }}-300 p-1 px-2 rounded text-{{ $forum->color }}-950 hover:shadow-lg leading-none"
       href="/forums/{{ $forum->slug }}">
        {{ $forum->name }}
    </a>
</div>
