<x-app-layout>
    <x-slot name="title">{{ $forum->title }} - Forum Archive</x-slot>
    <x-slot name="meta">{{ $forum->description ?: "Browse archived threads from {$forum->title} on OhioChatter." }}</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Forum Archive - {{ $forum->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <script async
                    src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-4406607721782655"
                    crossorigin="anonymous"></script>
            <!-- OC Bottom Ad -->
            <ins class="adsbygoogle"
                 style="display:block"
                 data-ad-client="ca-pub-4406607721782655"
                 data-ad-slot="3473533118"
                 data-ad-format="auto"
                 data-full-width-responsive="true"></ins>
            <script>
                (adsbygoogle = window.adsbygoogle || []).push({});
            </script>
            <x-archive-breadcrumbs :items="[
                ['title' => $forum->title]
            ]"/>
            <div class="space-y-4">
                @foreach($threads as $thread)
                    <div class="bg-slate-800 shadow-lg rounded-lg overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-start gap-4">
                                <!-- Author Avatar -->
                                <div class="flex-shrink-0">
                                    @if ($thread->creator && $thread->creator->avatar)
                                        <img class="rounded-full h-12 w-12 object-cover"
                                             src="/storage/avatars/archive/{{ $thread->creator->avatar->filename }}"
                                             alt="{{ $thread->creator->username }}'s avatar"/>
                                    @else
                                        <div
                                            class="rounded-full h-12 w-12 bg-slate-700 flex items-center justify-center">
                                            <span class="text-lg text-gray-400">
                                                {{ strtoupper(substr($thread->creator?->username ?? 'G', 0, 1)) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Thread Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <a href="{{ route('archive.thread', $thread) }}"
                                           class="text-xl font-semibold text-white hover:text-blue-400 transition-colors truncate">
                                            {{ $thread->title }}
                                        </a>
                                    </div>

                                    <div class="mt-1 flex items-center gap-4 text-sm text-gray-400">
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                            {{ $thread->creator?->username ?? 'Guest' }}
                                        </span>
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                                 viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ date('M j, Y g:ia', $thread->dateline) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Thread Stats -->
                                <div class="flex items-center gap-6 text-sm text-gray-400">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                        </svg>
                                        {{ number_format($thread->replycount) }}
                                        <span class="sr-only">replies</span>
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        {{ number_format($thread->views) }}
                                        <span class="sr-only">views</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Latest Reply Info -->
                            @if($thread->lastposter && $thread->lastpost != $thread->dateline)
                                <div class="mt-4 pt-4 border-t border-slate-700">
                                    <div class="flex items-center text-sm text-gray-400">
                                        <span class="mr-2">Latest reply by</span>
                                        <span class="font-medium text-gray-300">{{ $thread->lastposter }}</span>
                                        <span class="mx-2">•</span>
                                        <span>{{ date('M j, Y g:ia', $thread->lastpost) }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $threads->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
