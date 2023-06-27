<div class="flex order-0 font-headline">
{{--    <span class="text-blue-950 bg-blue-300 text-red-950 bg-red-300 text-orange-950 bg-orange-300"></span>--}}
    <a
        class="flex items-center justify-items bg-{{ $thread->forum->color }}-300 p-1 px-2 rounded text-{{ $thread->forum->color }}-950 text-lg md:text-sm hover:shadow-lg leading-none"
        href="/forums/{{ $thread->forum->slug }}"
    >
        {{ $thread->forum->name }}
    </a>
</div>
