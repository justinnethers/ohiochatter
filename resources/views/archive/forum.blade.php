<x-app-layout>
    <x-slot name="title">{{ $forum->title }} - Forum Archive</x-slot>
    <x-slot name="meta">{{ $forum->description ?: "Browse archived threads from {$forum->title} on OhioChatter." }}</x-slot>
    <x-slot name="header">
        <h2 class="font-bold text-3xl text-white leading-tight flex items-center gap-3">
            <span class="hidden md:inline-block w-1 h-8 bg-accent-500 rounded-full"></span>
            {{ $forum->title }}
        </h2>
    </x-slot>

    <div class="container mx-auto">
        <div class="md:rounded-2xl md:bg-gradient-to-br md:from-steel-800/50 md:to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-2 md:p-8 md:mt-4">
            {{ $threads->links('pagination::tailwind', ['top' => true]) }}

            <x-breadcrumbs :items="[
                ['title' => 'Archive', 'url' => route('archive.index')],
                ['title' => $forum->title],
            ]"/>

            <div class="bg-steel-800/50 p-3 rounded-xl mb-4 shadow-lg shadow-black/20 border border-steel-700/30">
                <ins class="adsbygoogle"
                     style="display:block"
                     data-ad-client="ca-pub-4406607721782655"
                     data-ad-slot="3473533118"
                     data-ad-format="auto"
                     data-full-width-responsive="true"></ins>
                <script>
                    (adsbygoogle = window.adsbygoogle || []).push({});
                </script>
            </div>

            <section>
                @php
                    $adSlots = ['2001567130', '2900286656', '2521012709', '5660018222', '7961041643'];
                    $adFrequency = 5; // Show ad every 5 threads
                @endphp
                @foreach($threads as $index => $thread)
                    <article class="group bg-gradient-to-br from-steel-800 to-steel-850 p-4 text-steel-100 font-body rounded-xl mb-3 md:mb-5 shadow-lg shadow-black/20 border border-steel-700/50 hover:border-steel-600 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 relative overflow-hidden">
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-accent-400 to-accent-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                        <a class="text-lg md:text-xl hover:text-accent-400 text-white font-semibold transition-colors duration-200 block" href="{{ route('archive.thread', $thread) }}">
                            {{ $thread->title }}
                        </a>

                        <div class="md:flex text-sm md:text-base justify-between rounded-lg my-3 bg-steel-900/50 shadow-inner divide-y md:divide-y-0 divide-steel-700/50">
                            <div class="flex items-center space-x-2 py-2 px-3">
                                @if ($thread->creator && $thread->creator->avatar)
                                    <img class="rounded-full h-6 w-6 object-cover ring-2 ring-steel-700"
                                         src="/storage/avatars/archive/{{ $thread->creator->avatar->filename }}"
                                         alt="{{ $thread->creator->username }}'s avatar"/>
                                @else
                                    <div class="rounded-full h-6 w-6 bg-steel-700 flex items-center justify-center ring-2 ring-steel-600">
                                        <span class="text-xs text-steel-400">
                                            {{ strtoupper(substr($thread->creator?->username ?? 'G', 0, 1)) }}
                                        </span>
                                    </div>
                                @endif
                                <span class="text-steel-300">
                                    {{ date('M j, Y', $thread->dateline) }}
                                    <span class="text-accent-400 font-medium">{{ $thread->creator?->username ?? 'Guest' }}</span>
                                </span>
                            </div>

                            @if($thread->lastposter && $thread->lastpost != $thread->dateline)
                                <div class="flex items-center justify-end space-x-2 py-2 px-3">
                                    <div class="text-right text-steel-300">
                                        <span class="text-steel-400">{{ date('M j, Y', $thread->lastpost) }}</span>
                                        <span class="text-accent-400 font-medium">{{ $thread->lastposter }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-wrap text-sm text-steel-400 mt-2">
                            <span class="mr-4">{{ number_format($thread->replycount) }} replies</span>
                            <span>{{ number_format($thread->views) }} views</span>
                        </div>
                    </article>

                    {{-- In-content ad every 5 threads --}}
                    @if(($index + 1) % $adFrequency === 0 && $index < count($threads) - 1)
                        <div class="bg-steel-800/50 p-3 rounded-xl mb-3 md:mb-5 shadow-lg shadow-black/20 border border-steel-700/30">
                            <ins class="adsbygoogle"
                                 style="display:block"
                                 data-ad-client="ca-pub-4406607721782655"
                                 data-ad-slot="{{ $adSlots[$loop->iteration % count($adSlots)] }}"
                                 data-ad-format="auto"
                                 data-full-width-responsive="true"></ins>
                            <script>
                                (adsbygoogle = window.adsbygoogle || []).push({});
                            </script>
                        </div>
                    @endif
                @endforeach
            </section>

            {{ $threads->links('pagination::tailwind', ['top' => false]) }}
        </div>
    </div>
</x-app-layout>
