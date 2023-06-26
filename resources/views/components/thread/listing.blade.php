<article class="bg-gray-700 px-4 pt-4 pb-5 text-gray-100 font-body rounded-md mb-2 shadow">

    <a class="text-2xl hover:underline text-gray-200" href="/forums/{{ $forum->slug }}/{{ $thread->slug }}">
        @if (auth()->check() && auth()->user()->hasRepliedTo($thread))
            <span class="">&raquo;</span>
        @endif
        {{ $thread->title }}
    </a>

    <div class="md:flex justify-between rounded-md p-2 mt-3 mb-4 bg-gray-800 shadow">

        <div class="flex items-center space-x-2">
            @if ($thread->owner)
                <x-avatar size="8" :avatar-path="$thread->owner->avatar_path" />
                <span class="leading-none">started {{ \Carbon\Carbon::parse($thread->created_at)->setTimezone((auth()->check() ? auth()->user()->timezone : null))->diffForHumans() }} by {{ $thread->owner->username }}</span>
            @endif
        </div>

        <div class="flex items-center justify-end space-x-2 bg-main-color posted-by-when rounded shadow md:shadow-none p-2 md:p-0 mb-2 md:m-0">
            @if ($thread->replies->last())
                <div class="text-right">
                    <span class="md:mr-1">last post was</span>
                    <span class="md:mr-1">{{ \Carbon\Carbon::parse($thread->replies->last()->created_at)->setTimezone((auth()->check() ? auth()->user()->timezone : null))->diffForHumans() }} by</span>
                    <a href="/profiles/{{ $thread->replies->last()->owner->username }}" class="text-blue-500 hover:underline">{{ $thread->replies->last()->owner->username }}</a>
                </div>
                <x-avatar size="8" :avatar-path="$thread->replies->last()->owner->avatar_path" />
            @else
                <div>
                    <span class="md:mr-1">last post was</span>
                    <a href="/profiles/{{ $thread->owner->username }}">{{ $thread->owner->username }}</a>
                    <span>{{ \Carbon\Carbon::parse($thread->created_at)->setTimezone((auth()->check() ? auth()->user()->timezone : null))->diffForHumans() }}</span>
                </div>
                <x-avatar size="8" :avatar-path="$thread->owner->avatar_path" />
            @endif
        </div>

    </div>

    <div class="flex flex-wrap md:text-sm mt-6 md:mt-2">
        <x-thread.forum-tag :$thread />
        <x-thread.pagination :$thread />
        <div class="md:flex-1 md:order-2"></div>
        <x-thread.stats :$thread />
    </div>

</article>
