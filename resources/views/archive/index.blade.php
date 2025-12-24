<x-app-layout>
    <x-slot name="title">Forum Archive - OhioChatter</x-slot>
    <x-slot name="meta">Browse the OhioChatter forum archive. Explore classic discussions from Ohio's online community.</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-3xl text-gray-200 leading-tight">
            Forum Archive
        </h2>
    </x-slot>

    <div class="container mx-auto">
        <div class="md:rounded-lg md:bg-gray-800 p-2 md:p-8 md:pt-4 md:mt-4">
            <x-archive-breadcrumbs :items="[]"/>

            <div class="bg-gray-700 p-3 rounded md:rounded-md mb-4 shadow-lg">
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

            @foreach($forums as $forum)
                <article class="bg-gray-700 p-3 text-gray-100 font-body rounded md:rounded-md mb-2 md:mb-6 shadow-lg">
                    <a class="text-xl hover:underline text-gray-200" href="{{ route('archive.forum', $forum) }}">
                        {{ $forum->title }}
                    </a>

                    @if($forum->description)
                        <p class="text-gray-400 text-sm mt-1">{{ $forum->description }}</p>
                    @endif

                    <div class="md:flex text-base justify-between rounded md:rounded-md my-2 bg-gray-800 shadow divide-y md:divide-y-0 divide-gray-700">
                        <div class="flex items-center space-x-2 py-1.5 px-2">
                            <span class="text-gray-400">
                                {{ number_format($forum->threadcount) }} threads
                                &middot;
                                {{ number_format($forum->replycount) }} replies
                            </span>
                        </div>

                        @if($forum->latest_thread_title)
                            <div class="flex items-center justify-end space-x-2 py-1.5 px-2">
                                <div class="text-right">
                                    <span class="text-gray-400 text-sm">Latest:</span>
                                    <a href="{{ route('archive.thread', $forum->latest_thread_id) }}"
                                       class="text-blue-500 hover:underline">
                                        {{ Str::limit($forum->latest_thread_title, 40) }}
                                    </a>
                                    <span class="text-gray-500 text-sm">
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
        </div>
    </div>
</x-app-layout>
