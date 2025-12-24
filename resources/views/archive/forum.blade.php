<x-app-layout>
    <x-slot name="title">{{ $forum->title }} - Forum Archive</x-slot>
    <x-slot name="meta">{{ $forum->description ?: "Browse archived threads from {$forum->title} on OhioChatter." }}</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-3xl text-gray-200 leading-tight">
            {{ $forum->title }}
        </h2>
    </x-slot>

    <div class="container mx-auto">
        <div class="md:rounded-lg md:bg-gray-800 p-2 md:p-8 md:pt-4 md:mt-4">
            {{ $threads->links('pagination::tailwind', ['top' => true]) }}

            <x-archive-breadcrumbs :items="[
                ['title' => $forum->title]
            ]"/>

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

            <section>
                @foreach($threads as $thread)
                    <article class="bg-gray-700 p-3 text-gray-100 font-body rounded md:rounded-md mb-2 md:mb-6 shadow-lg">
                        <a class="text-xl hover:underline text-gray-200" href="{{ route('archive.thread', $thread) }}">
                            {{ $thread->title }}
                        </a>

                        <div class="md:flex text-base justify-between rounded md:rounded-md my-2 bg-gray-800 shadow divide-y md:divide-y-0 divide-gray-700">
                            <div class="flex items-center space-x-2 py-1.5 px-2">
                                @if ($thread->creator && $thread->creator->avatar)
                                    <img class="rounded-full h-6 w-6 object-cover"
                                         src="/storage/avatars/archive/{{ $thread->creator->avatar->filename }}"
                                         alt="{{ $thread->creator->username }}'s avatar"/>
                                @else
                                    <div class="rounded-full h-6 w-6 bg-gray-600 flex items-center justify-center">
                                        <span class="text-xs text-gray-400">
                                            {{ strtoupper(substr($thread->creator?->username ?? 'G', 0, 1)) }}
                                        </span>
                                    </div>
                                @endif
                                <span>
                                    {{ date('M j, Y', $thread->dateline) }}
                                    <span class="text-blue-500">{{ $thread->creator?->username ?? 'Guest' }}</span>
                                </span>
                            </div>

                            @if($thread->lastposter && $thread->lastpost != $thread->dateline)
                                <div class="flex items-center justify-end space-x-2 py-1.5 px-2">
                                    <div class="text-right">
                                        <span class="text-gray-400">{{ date('M j, Y', $thread->lastpost) }}</span>
                                        <span class="text-blue-500">{{ $thread->lastposter }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="flex flex-wrap text-sm text-gray-400 mt-2">
                            <span class="mr-4">{{ number_format($thread->replycount) }} replies</span>
                            <span>{{ number_format($thread->views) }} views</span>
                        </div>
                    </article>
                @endforeach
            </section>

            {{ $threads->links('pagination::tailwind', ['top' => false]) }}
        </div>
    </div>
</x-app-layout>
