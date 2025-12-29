<x-app-layout>
    <x-slot name="title">{{ $forum->name }}</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-lg md:text-xl font-bold text-white leading-tight flex items-center gap-3">
                <span class="hidden md:inline-block w-1 h-6 bg-accent-500 rounded-full"></span>
                {{ $forum->name }}
            </h2>
            @if (auth()->check())
                <a href="/forums/{{ $forum->slug }}/threads/create"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 md:px-4 md:py-2 bg-gradient-to-r from-accent-500 to-accent-600 rounded-lg text-white font-semibold text-xs md:text-sm shadow-lg shadow-accent-500/25 hover:shadow-accent-500/40 hover:from-accent-600 hover:to-accent-700 transition-all duration-200 whitespace-nowrap shrink-0">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>New Thread</span>
                </a>
            @endif
        </div>
    </x-slot>

    <div class="container mx-auto">
        <div class="md:rounded-2xl md:bg-gradient-to-br md:from-steel-800/50 md:to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-2 md:p-8 md:pt-6 md:mt-4">
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
                        <article class="bg-gradient-to-br from-steel-800 to-steel-850 p-4 rounded-xl mb-3 md:mb-5 shadow-lg shadow-black/20 border border-steel-700/50">
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
