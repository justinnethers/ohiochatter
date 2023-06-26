<div class="mb-2 md:m-0 w-1/2 md:w-auto order-0 font-headline">
{{--    <span class="text-blue-950 bg-blue-300 text-red-950 bg-red-300 text-orange-950 bg-orange-300"></span>--}}
    <a class="bg-{{ $thread->forum->color }}-300 p-1 px-2 rounded text-{{ $thread->forum->color }}-950 text-lg md:text-sm inline-block hover:shadow" href="/forums/{{ $thread->forum->slug }}">{{ $thread->forum->name }}</a>
</div>
