<x-app-layout :seo="$seo ?? null">
    <x-slot name="title">Forum Archive - OhioChatter</x-slot>
    <x-slot name="meta">Browse the OhioChatter forum archive. Explore classic discussions from Ohio's online community.</x-slot>
    <x-slot name="header">
        <h2 class="font-bold text-3xl text-white leading-tight flex items-center gap-3">
            <span class="hidden md:inline-block w-1 h-8 bg-accent-500 rounded-full"></span>
            Forum Archive
        </h2>
    </x-slot>

    <div class="container mx-auto">
        <div class="md:rounded-2xl md:bg-gradient-to-br md:from-steel-800/50 md:to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-2 md:p-8 md:mt-4">
            <x-breadcrumbs :items="[
                ['title' => 'Archive'],
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

            @foreach($groupedForums as $group)
                <h3 class="text-xl font-semibold text-steel-200 mb-3 mt-6 first:mt-0 flex items-center gap-2">
                    <span class="w-1 h-5 bg-accent-500 rounded-full"></span>
                    {{ $group['name'] }}
                </h3>

                @foreach($group['forums'] as $forum)
                    <article class="group bg-gradient-to-br from-steel-800 to-steel-850 p-4 text-steel-100 font-body rounded-xl mb-3 md:mb-4 shadow-lg shadow-black/20 border border-steel-700/50 hover:border-steel-600 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 relative overflow-hidden">
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-accent-400 to-accent-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                        <a class="text-xl hover:text-accent-400 text-white font-semibold transition-colors duration-200" href="{{ route('archive.forum', $forum) }}">
                            {{ $forum->title }}
                        </a>

                        @if($forum->description)
                            <p class="text-steel-400 text-sm mt-1">{{ $forum->description }}</p>
                        @endif

                        <div class="md:flex text-base justify-between rounded-lg my-3 bg-steel-900/50 shadow-inner divide-y md:divide-y-0 divide-steel-700/50">
                            <div class="flex items-center space-x-2 py-2 px-3">
                                <span class="text-steel-400">
                                    {{ number_format($forum->threadcount) }} threads
                                    &middot;
                                    {{ number_format($forum->replycount) }} replies
                                </span>
                            </div>

                            @if($forum->latest_thread_title)
                                <div class="flex items-center justify-end space-x-2 py-2 px-3">
                                    <div class="text-right">
                                        <span class="text-steel-400 text-sm">Latest:</span>
                                        <a href="{{ url('archive/thread/' . $forum->latest_thread_id . '-' . Str::slug($forum->latest_thread_title)) }}"
                                           class="text-accent-400 hover:text-accent-300 transition-colors duration-200">
                                            {{ Str::limit($forum->latest_thread_title, 40) }}
                                        </a>
                                        <span class="text-steel-500 text-sm">
                                            by {{ $forum->latest_thread_poster }}
                                            &middot;
                                            {{ date('M j, Y', $forum->latest_thread_lastpost) }}
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            @endforeach
        </div>
    </div>
</x-app-layout>
