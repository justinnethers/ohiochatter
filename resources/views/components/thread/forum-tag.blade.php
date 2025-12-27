@props(['thread'])
@php
    $forum = Cache::rememberForever("forum_{$thread->forum->id}", fn() => $thread->forum);
@endphp
<div class="flex order-0 flex-1 md:flex-none">
    <a class="inline-flex items-center px-3 py-1 bg-{{ $forum->color }}-500 rounded-full text-sm font-semibold text-white shadow-lg shadow-black/20 hover:bg-{{ $forum->color }}-600 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200"
       href="/forums/{{ $forum->slug }}">
        {{ $forum->name }}
    </a>
</div>
