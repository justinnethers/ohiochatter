<x-app-layout>
    <x-slot name="title">{{ $thread->title }} - Forum Archive</x-slot>
    <x-slot name="meta">{{ Str::limit(strip_tags($posts->first()?->pagetext ?? ''), 160) }}</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-gray-200 leading-tight">
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

            @foreach($posts as $post)
                <article class="bg-gray-800 text-white mb-4 md:flex rounded md:rounded-lg relative">
                    {{-- Desktop: User sidebar --}}
                    <section class="hidden w-64 md:flex flex-col items-center p-8 space-y-4 text-white">
                        <div class="text-center">
                            <h3 class="text-2xl text-gray-200 font-bold leading-tight">{{ $post->username }}</h3>
                            @if($post->creator)
                                <h4 class="text-gray-400">{{ $post->creator->usertitle ?? '' }}</h4>
                            @endif
                        </div>

                        @if($post->creator && $post->creator->avatar)
                            <img class="rounded-full h-24 w-24 object-cover"
                                 src="/storage/avatars/archive/{{ $post->creator->avatar->filename }}"
                                 alt="{{ $post->username }}'s avatar" />
                        @else
                            <div class="rounded-full h-24 w-24 bg-gray-700 flex items-center justify-center">
                                <span class="text-3xl text-gray-400">
                                    {{ strtoupper(substr($post->username, 0, 1)) }}
                                </span>
                            </div>
                        @endif

                        @if($post->creator)
                            <div class="text-center">
                                <div>
                                    <span class="font-bold italic">{{ number_format($post->creator->posts) }}</span>
                                    posts
                                </div>
                            </div>
                        @endif
                    </section>

                    {{-- Mobile: User header --}}
                    <section class="md:hidden flex items-center p-4 space-x-4 text-white">
                        @if($post->creator && $post->creator->avatar)
                            <img class="rounded-full h-12 w-12 object-cover"
                                 src="/storage/avatars/archive/{{ $post->creator->avatar->filename }}"
                                 alt="{{ $post->username }}'s avatar" />
                        @else
                            <div class="rounded-full h-12 w-12 bg-gray-700 flex items-center justify-center">
                                <span class="text-lg text-gray-400">
                                    {{ strtoupper(substr($post->username, 0, 1)) }}
                                </span>
                            </div>
                        @endif
                        <div>
                            <h3 class="text-xl text-gray-200 font-bold leading-tight">{{ $post->username }}</h3>
                            @if($post->creator)
                                <div class="text-xs text-gray-400">
                                    <span class="font-bold italic">{{ number_format($post->creator->posts) }}</span> posts
                                </div>
                            @endif
                        </div>
                    </section>

                    <div class="flex-1 flex flex-col relative">
                        {{-- Post header with date --}}
                        <div class="bg-gray-700 px-4 py-2 text-sm text-gray-400 hidden md:block md:rounded-bl-lg md:rounded-tr-lg">
                            {{ date('M j, Y g:ia', $post->dateline) }}
                        </div>

                        {{-- Post content --}}
                        <div class="p-4 md:p-8 flex-1">
                            <div class="post-body text-xl md:text-lg">
                                {!! parseBBCode($post->pagetext) !!}
                            </div>
                        </div>

                        {{-- Mobile date --}}
                        <div class="md:hidden px-4 pb-4 text-sm text-gray-500">
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
