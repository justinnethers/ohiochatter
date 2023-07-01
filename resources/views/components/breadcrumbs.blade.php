<div class="bg-gray-800 md:rounded-lg p-2 md:px-6 my-1.5 md:my-4 font-headline font-bold text-xl text-white">
    @if ($forum)
        <a href="/">Home</a>
        <span >&blacktriangleright;</span>
        <a href="/forums">Forums</a>
        <span>&blacktriangleright;</span>
        <a href="/forums/{{ $forum->slug }}">{{ $forum->name }}</a>
    @endif
</div>
