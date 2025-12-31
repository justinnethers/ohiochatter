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
            <x-breadcrumbs :items="[
                ['title' => 'Archive', 'url' => route('archive.index')],
                ['title' => $thread->forum->title, 'url' => route('archive.forum', $thread->forum)],
                ['title' => $thread->title],
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

            @php
                $adSlots = ['2001567130', '2900286656', '2521012709', '5660018222', '7961041643'];
                $adFrequency = 5; // Show ad every 5 posts
            @endphp
            @foreach($posts as $index => $post)
                <x-post.card>
                    <x-post.owner
                        :owner="null"
                        :username="$post->username"
                        :usertitle="$post->creator?->usertitle"
                        :archive-avatar-filename="$post->creator?->avatar?->filename"
                        :posts-count="$post->creator?->posts"
                        :link-profile="false"
                    />
                    <div class="flex-1 flex flex-col relative">
                        <x-post.header :date="\Carbon\Carbon::createFromTimestamp($post->dateline)" />

                        {{-- Post content --}}
                        <div class="prose prose-invert prose-lg p-4 md:p-8 flex-1 post-body">
                            {!! parseBBCode($post->pagetext) !!}
                        </div>

                        {{-- Mobile date --}}
                        <div class="md:hidden px-4 pb-4 text-sm text-steel-500">
                            {{ date('M j, Y g:ia', $post->dateline) }}
                        </div>
                    </div>
                </x-post.card>

                {{-- In-content ad every 5 posts --}}
                @if(($index + 1) % $adFrequency === 0 && $index < count($posts) - 1)
                    <div class="bg-steel-800/50 p-3 rounded-xl mb-4 shadow-lg shadow-black/20 border border-steel-700/30">
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
        </div>

        {{ $posts->links() }}

        <div class="h-8"></div>
    </div>
</x-app-layout>
