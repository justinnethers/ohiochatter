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
                >Create Thread
                </x-nav-link>
            @endif
        </div>
    </x-slot>

    <div class="container mx-auto">
        <div class="md:rounded-lg md:bg-gray-800 p-2 md:p-8 md:pt-4 md:mt-4">
            {{ $threads->links('pagination::tailwind', ['top' => true]) }}
            <section>
                @php
                    $check = Auth::check() ? 10 : 4;
                    $count = 0;
                    $adSlots = ['2001567130', '2900286656', '2521012709', '5660018222', '7961041643'];
                @endphp
                @foreach ($threads as $thread)
                    <x-thread.listing :$thread :$forum/>
                    @if (($loop->index + 1) % $check === 0)
                        <article
                            class="bg-gray-700 p-3 md:px-4 md:pt-4 md:pb-5 text-gray-100 font-body rounded md:rounded-md mb-2 md:mb-6 shadow-lg">
                            <script async
                                    src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-4406607721782655"
                                    crossorigin="anonymous"></script>
                            <!-- In-listing Ad -->
                            <ins class="adsbygoogle"
                                 style="display:block"
                                 data-ad-client="ca-pub-4406607721782655"
                                 data-ad-slot="{{ $adSlots[$count] }}"
                                 data-ad-format="auto"
                                 data-full-width-responsive="true"></ins>
                            <script>
                                (adsbygoogle = window.adsbygoogle || []).push({});
                            </script>
                        </article>
                        @php $count++ @endphp
                    @endif
                @endforeach
            </section>
            {{ $threads->links('pagination::tailwind', ['top' => false]) }}
        </div>
    </div>
</x-app-layout>
