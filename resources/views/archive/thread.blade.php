<x-app-layout>
    <x-slot name="title">{{ $thread->title }} - Forum Archive</x-slot>
    <x-slot name="meta">{{ Str::limit(strip_tags($posts->first()?->pagetext ?? ''), 160) }}</x-slot>
    <x-slot name="header">
        <h2 class="font-bold text-xl md:text-2xl text-white leading-tight flex items-center gap-3">
            <span class="hidden md:inline-block w-1 h-6 bg-accent-500 rounded-full"></span>
            {{ $thread->title }}
        </h2>
    </x-slot>

    <div class="container mx-auto">
        {{ $posts->links('pagination::tailwind', ['top' => true]) }}

        <div class="p-2 pt-0 md:p-0">
            <x-archive-breadcrumbs :items="[
                ['title' => $thread->forum->title, 'url' => route('archive.forum', $thread->forum)],
                ['title' => $thread->title]
            ]" />

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

            @foreach($posts as $post)
                <article class="bg-gradient-to-br from-steel-800 to-steel-850 text-white mb-5 md:flex rounded-xl relative border border-steel-700/50 shadow-xl shadow-black/20 overflow-hidden">
                    {{-- Subtle top accent --}}
                    <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-steel-600/50 to-transparent"></div>

                    {{-- Desktop: User sidebar --}}
                    <section class="hidden w-64 md:flex flex-col items-center p-8 space-y-4 text-white border-r border-steel-700/30">
                        <div class="text-center">
                            <h3 class="text-xl text-white font-bold leading-tight">{{ $post->username }}</h3>
                            @if($post->creator)
                                <h4 class="text-steel-400 text-sm">{{ $post->creator->usertitle ?? '' }}</h4>
                            @endif
                        </div>

                        @if($post->creator && $post->creator->avatar)
                            <img class="rounded-full h-24 w-24 object-cover ring-4 ring-steel-700 shadow-lg"
                                 src="/storage/avatars/archive/{{ $post->creator->avatar->filename }}"
                                 alt="{{ $post->username }}'s avatar" />
                        @else
                            <div class="rounded-full h-24 w-24 bg-steel-700 flex items-center justify-center ring-4 ring-steel-600 shadow-lg">
                                <span class="text-3xl text-steel-400">
                                    {{ strtoupper(substr($post->username, 0, 1)) }}
                                </span>
                            </div>
                        @endif

                        @if($post->creator)
                            <div class="text-center text-steel-400">
                                <div>
                                    <span class="font-bold text-steel-300">{{ number_format($post->creator->posts) }}</span>
                                    posts
                                </div>
                            </div>
                        @endif
                    </section>

                    {{-- Mobile: User header --}}
                    <section class="md:hidden flex items-center p-4 space-x-4 text-white border-b border-steel-700/30">
                        @if($post->creator && $post->creator->avatar)
                            <img class="rounded-full h-12 w-12 object-cover ring-2 ring-steel-700"
                                 src="/storage/avatars/archive/{{ $post->creator->avatar->filename }}"
                                 alt="{{ $post->username }}'s avatar" />
                        @else
                            <div class="rounded-full h-12 w-12 bg-steel-700 flex items-center justify-center ring-2 ring-steel-600">
                                <span class="text-lg text-steel-400">
                                    {{ strtoupper(substr($post->username, 0, 1)) }}
                                </span>
                            </div>
                        @endif
                        <div>
                            <h3 class="text-lg text-white font-bold leading-tight">{{ $post->username }}</h3>
                            @if($post->creator)
                                <div class="text-xs text-steel-400">
                                    <span class="font-bold text-steel-300">{{ number_format($post->creator->posts) }}</span> posts
                                </div>
                            @endif
                        </div>
                    </section>

                    <div class="flex-1 flex flex-col relative">
                        {{-- Post header with date --}}
                        <div class="bg-steel-900/50 px-4 py-2 text-sm text-steel-400 hidden md:block">
                            {{ date('M j, Y g:ia', $post->dateline) }}
                        </div>

                        {{-- Post content --}}
                        <div class="prose prose-invert prose-lg p-4 md:p-8 flex-1 post-body">
                            {!! parseBBCode($post->pagetext) !!}
                        </div>

                        {{-- Mobile date --}}
                        <div class="md:hidden px-4 pb-4 text-sm text-steel-500">
                            {{ date('M j, Y g:ia', $post->dateline) }}
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        {{ $posts->links() }}

        <div class="h-8"></div>
    </div>
</x-app-layout>
