@props(['thread'])
@php
    $forum = Cache::remember("forum_{$thread->forum->id}", 3600, fn() => $thread->forum);
@endphp
{{--    <span class="text-blue-950 bg-blue-300 text-red-950 bg-red-300 text-green-950 bg-green-300 text-orange-950 bg-orange-300"></span>--}}
<div class="flex order-0 flex-1 md:flex-none">
    <a class="flex items-center justify-items bg-{{ $forum->color }}-300 p-1 px-2 rounded text-{{ $forum->color }}-950 hover:shadow-lg leading-none"
       href="/forums/{{ $forum->slug }}">
        {{ $forum->name }}
    </a>
</div>
