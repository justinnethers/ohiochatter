<div class="bg-gray-800 rounded-md p-2 px-4 mb-4 font-headline font-bold text-sm md:text-base text-white">
    @if ($forum)
        <a href="/">Home</a>
        <span>&blacktriangleright;</span>
        <a href="/forums">Forums</a>
        <span>&blacktriangleright;</span>
        <a href="/forums/{{ $forum->slug }}">{{ $forum->name }}</a>
    @endif
</div>
