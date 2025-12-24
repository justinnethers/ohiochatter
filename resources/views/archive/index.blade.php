<x-app-layout>
    <x-slot name="title">Forum Archive - OhioChatter</x-slot>
    <x-slot name="meta">Browse the OhioChatter forum archive. Explore classic discussions from Ohio's online community.</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Forum Archive
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
            <x-archive-breadcrumbs :items="[]"/>
            <div class="grid grid-cols-1 gap-6">
                @foreach($forums as $forum)
                    <div class="bg-slate-800 shadow-lg rounded-lg overflow-hidden">
                        <!-- Forum Header -->
                        <div class="p-6">
                            <a href="{{ route('archive.forum', $forum) }}" class="block">
                                <h3 class="text-xl font-bold text-white hover:text-blue-400 transition-colors">
                                    {{ $forum->title }}
                                </h3>
                                <p class="mt-2 text-gray-400 text-sm line-clamp-2">
                                    {{ $forum->description }}
                                </p>
                            </a>
                        </div>

                        <!-- Latest Thread Section -->
                        <div class="border-t border-slate-700 px-6 py-4">
                            @if($forum->latest_thread_title)
                                <div class="space-y-2">
                                    <div class="flex items-center text-sm text-gray-400">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ date('M j, Y', $forum->latest_thread_lastpost) }}
                                    </div>

                                    <a href="{{ route('archive.thread', $forum->latest_thread_id) }}"
                                       class="block group">
                                        <h4 class="text-blue-400 group-hover:text-blue-300 font-medium line-clamp-1">
                                            {{ $forum->latest_thread_title }}
                                        </h4>
                                        <p class="text-sm text-gray-500 mt-1">
                                            by {{ $forum->latest_thread_poster }}
                                        </p>
                                    </a>
                                </div>
                            @else
                                <p class="text-gray-500 text-sm italic">
                                    No discussions yet
                                </p>
                            @endif
                        </div>

                        <!-- Forum Stats -->
                        <div class="bg-slate-900 px-6 py-4">
                            <div class="flex justify-between text-sm text-gray-400">
                                <span>{{ number_format($forum->threadcount) }} threads</span>
                                <span>{{ number_format($forum->replycount) }} replies</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
