<div class="bg-gray-800 rounded-lg p-2 px-6 my-4 font-headline text-xl text-white">
    @if ($forum)
        <a href="/">Home</a>
        <span >&blacktriangleright;</span>
        <a href="/forums">Forums</a>
        <span>&blacktriangleright;</span>
        <a href="/forums/{{ $forum->slug }}">{{ $forum->name }}</a>
    @endif
</div>
