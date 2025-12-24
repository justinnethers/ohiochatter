<x-app-layout>
    <x-slot name="title">{{ $thread->title }} - Forum Archive</x-slot>
    <x-slot name="meta">{{ Str::limit(strip_tags($posts->first()?->pagetext ?? ''), 160) }}</x-slot>
    <x-slot name="header">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-1">
                <h2 class="font-semibold text-3xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ $thread->title }}
                </h2>
                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                    <a href="/archive/forum/{{ $thread->forum->forumid }}" class="hover:text-blue-400 transition-colors">
                        {{ $thread->forum->title }}
                    </a>
                    <span>•</span>
                    <span>{{ number_format($thread->replycount) }} replies</span>
                    <span>•</span>
                    <span>{{ number_format($thread->views) }} views</span>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-4406607721782655"
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
                ['title' => $thread->forum->title, 'url' => route('archive.forum', $thread->forum)],
                ['title' => $thread->title]
            ]" />
            <div class="space-y-6">
                @foreach($posts as $post)
                    <div class="bg-slate-800 shadow-lg rounded-lg overflow-hidden">
                        <!-- Post Header -->
                        <div class="bg-slate-900 px-6 py-4 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                @if($post->creator && $post->creator->avatar)
                                    <img class="rounded-full h-10 w-10 object-cover"
                                         src="/storage/avatars/archive/{{ $post->creator->avatar->filename }}"
                                         alt="{{ $post->username }}'s avatar" />
                                @else
                                    <div class="rounded-full h-10 w-10 bg-slate-700 flex items-center justify-center">
                                        <span class="text-lg text-gray-400">
                                            {{ strtoupper(substr($post->username, 0, 1)) }}
                                        </span>
                                    </div>
                                @endif
                                <div>
                                    <div class="font-medium text-white">{{ $post->username }}</div>
                                    @if($post->creator)
                                        <div class="text-sm text-gray-400">
                                            Posts: {{ number_format($post->creator->posts) }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="text-sm text-gray-400">
                                {{ date('M j, Y g:ia', $post->dateline) }}
                            </div>
                        </div>

                        <!-- Post Content -->
                        <div class="p-6">
                            <div class="prose prose-invert max-w-none">
                                {!! parseBBCode($post->pagetext) !!}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $posts->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

<style scoped>
    .prose blockquote {
        @apply border-l-4 border-slate-600 pl-4 py-1 my-4;
    }

    .prose cite {
        @apply block text-sm text-gray-400 mt-2;
    }
</style>
