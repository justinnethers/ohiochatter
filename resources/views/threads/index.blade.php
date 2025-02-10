<x-app-layout>
    <x-slot name="title">All Threads</x-slot>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-gray-200 dark:text-gray-200 leading-tight">
                All Threads
            </h2>
            @if (auth()->check())
                <x-nav-link
                    href="/threads/create"
                >Create Thread</x-nav-link>
            @endif
        </div>

    </x-slot>

    <div>
        <div class="md:rounded-lg md:bg-gray-800 p-2 md:p-8 md:pt-4 md:mt-4">
            {{ $threads->links('pagination::tailwind', ['top' => true]) }}
            <section class="container">
                @foreach ($threads as $thread)
                    <x-thread.listing :$thread />
                @endforeach
            </section>
            {{ $threads->links('pagination::tailwind', ['top' => false]) }}

            <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-4406607721782655"
                    crossorigin="anonymous"></script>
            <ins class="adsbygoogle"
                 style="display:block"
                 data-ad-format="autorelaxed"
                 data-ad-client="ca-pub-4406607721782655"
                 data-ad-slot="6239544678"></ins>
            <script>
                (adsbygoogle = window.adsbygoogle || []).push({});
            </script>
        </div>
    </div>
</x-app-layout>
