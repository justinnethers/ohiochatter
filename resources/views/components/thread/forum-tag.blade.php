@props(['thread'])
@php
    $forum = Cache::rememberForever("forum_{$thread->forum->id}", fn() => $thread->forum);
@endphp
<div class="flex order-0 shrink-0">
    <a class="inline-flex items-center px-2 md:px-3 py-0.5 md:py-1 bg-{{ $forum->color }}-500 rounded-full text-xs md:text-sm font-semibold text-white shadow-lg shadow-black/20 hover:bg-{{ $forum->color }}-600 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 whitespace-nowrap"
       href="/forums/{{ $forum->slug }}">
        {{ $forum->name }}
    </a>
</div>
