<x-app-layout>
    <x-slot name="title">{{ $forum->name }}</x-slot>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-gray-200 dark:text-gray-200 leading-tight">
                {{ $forum->name }}
            </h2>
            @if (auth()->check())
                <x-nav-link
                    href="/forums/{{ $forum->slug }}/threads/create"
                >Create Thread</x-nav-link>
            @endif
        </div>
    </x-slot>

    <div class="md:rounded-lg md:bg-gray-800 p-2 md:p-8 md:pt-4 md:mt-4">
        {{ $threads->links('pagination::tailwind', ['top' => true]) }}
        <section>
            @foreach ($threads as $thread)
                <x-thread.listing :$thread :$forum />
                @if ($forum->name === 'Politics')
                    @if ($loop->index % 10 === 0)
                        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-4406607721782655"
                                crossorigin="anonymous"></script>
                        <!-- In-listing Ad -->
                        <ins class="adsbygoogle"
                             style="display:block"
                             data-ad-client="ca-pub-4406607721782655"
                             data-ad-slot="2001567130"
                             data-ad-format="auto"
                             data-full-width-responsive="true"></ins>
                        <script>
                            (adsbygoogle = window.adsbygoogle || []).push({});
                        </script>
                    @endif
                @endif
            @endforeach
        </section>
        {{ $threads->links('pagination::tailwind', ['top' => false]) }}
    </div>
</x-app-layout>
