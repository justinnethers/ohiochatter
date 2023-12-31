<article class="bg-gray-700 p-3 md:px-4 md:pt-4 md:pb-5 text-gray-100 font-body rounded md:rounded-md mb-2 md:mb-6 shadow-lg">

    <a class="text-2xl hover:underline text-gray-200" href="/forums/{{ $thread->forum->slug }}/{{ $thread->slug }}">
        @if ($thread->locked)
            <span class="text-2xl">🔒</span>
        @endif
        @if (auth()->check() && auth()->user()->hasRepliedTo($thread))
            <span class="">&raquo;</span>
        @endif
        @if (auth()->check() && $thread->hasUpdatesFor(auth()->user()))
            <span class="font-bold">
                @if ($thread->poll)Poll: @endif{{ $thread->title }}
            </span>
        @else
            @if ($thread->poll)Poll: @endif{{ $thread->title }}
        @endif
    </a>

    <div class="md:flex text-lg justify-between rounded md:rounded-md px-2 mt-3 mb-4 bg-gray-800 shadow">

        <div class="flex items-center space-x-2">
            @if ($thread->owner)
                <x-avatar size="8" :avatar-path="$thread->owner->avatar_path" />
                <span>
                    started {{ \Carbon\Carbon::parse($thread->created_at)->setTimezone((auth()->check() ? auth()->user()->timezone : null))->diffForHumans() }}
                    by <br class="md:hidden"><a href="/profiles/{{ $thread->owner->username }}" class="text-blue-500 hover:underline">{{ $thread->owner->username }}</a>
                </span>
            @endif
        </div>

        <hr class="border-gray-700 border-2 md:hidden mt-2 mb-1.5">

        <div class="flex items-center justify-end space-x-2 bg-main-color posted-by-when rounded shadow md:shadow-none md:p-2 md:p-0 md:m-0">
            @if ($thread->replies->last())
                <div class="text-right">
                    <span class="md:mr-1">last post was</span>
                    <span class="md:mr-1">
                        {{ \Carbon\Carbon::parse($thread->replies->last()->created_at)->setTimezone((auth()->check() ? auth()->user()->timezone : null))->diffForHumans() }}
                        by<br class="md:hidden">
                    </span>
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

    <div class="hidden md:flex flex-wrap text-lg md:mt-2">
        <x-thread.forum-tag :$thread />
        <x-thread.pagination :$thread />
        <div class="md:flex-1 md:order-2"></div>
        <x-thread.stats :$thread />
    </div>

    <div class="md:hidden">
        <div class="flex">
            <x-thread.forum-tag :$thread />
            <x-thread.stats :$thread />
        </div>
        <div class="h-4 md:hidden"></div>
        <x-thread.pagination :$thread />
        <div class="md:flex-1 md:order-2"></div>
    </div>

</article>
