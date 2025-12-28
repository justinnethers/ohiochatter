<article class="group bg-gradient-to-br from-steel-800 to-steel-850 p-4 text-steel-100 font-body rounded-xl mb-3 md:mb-5 shadow-lg shadow-black/20 border border-steel-700/50 hover:border-steel-600 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 relative overflow-hidden">
    {{-- Accent stripe on left --}}
    <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-accent-400 to-accent-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

    <a class="text-lg md:text-xl hover:text-accent-400 text-white font-semibold transition-colors duration-200 block" href="/forums/{{ $thread->forum->slug }}/{{ $thread->slug }}">
        @if ($thread->locked)
            <span class="text-steel-400 mr-1">🔒</span>
        @endif
        @if (auth()->check() && auth()->user()->hasRepliedTo($thread))
            <span class="text-accent-400 mr-1">&raquo;</span>
        @endif
        @if (auth()->check() && $thread->hasUpdatesFor(auth()->user()))
            <span class="font-bold text-white">
                <span class="inline-block w-2 h-2 bg-accent-400 rounded-full mr-2 animate-pulse"></span>
                @if ($thread->poll)
                    <span class="text-amber-400">Poll:</span>
                @endif{{ $thread->title }}
            </span>
        @else
            <span class="font-normal text-steel-300">
                @if ($thread->poll)
                    <span class="text-amber-400/70">Poll:</span>
                @endif{{ $thread->title }}
            </span>
        @endif
    </a>

    <div
        class="md:flex text-sm md:text-base justify-between rounded-lg my-3 bg-steel-900/50 shadow-inner divide-y md:divide-y-0 divide-steel-700/50">
        <div class="flex items-center space-x-2 py-2 px-3">
            @if ($thread->owner)
                <x-avatar size="6" :avatar-path="$thread->owner->avatar_path" class="ring-2 ring-steel-700"/>
                <span class="text-steel-300">
                    {{ \Carbon\Carbon::parse($thread->created_at)->setTimezone((auth()->check() ? auth()->user()->timezone : null))->diffForHumans() }}
                     <a href="/profiles/{{ $thread->owner->username }}"
                        class="text-accent-400 hover:text-accent-300 hover:underline font-medium">{{ $thread->owner->username }}</a>
                </span>
            @endif
        </div>

        <div
            class="flex items-center justify-end space-x-2 py-2 px-3">
            @if ($thread->lastReply)
                <div class="text-right text-steel-300">
                    <span class="md:mr-1">
                        {{ \Carbon\Carbon::parse($thread->lastReply->created_at)->setTimezone((auth()->check() ? auth()->user()->timezone : null))->diffForHumans() }}
                    </span>
                    <a href="/profiles/{{ $thread->lastReply->owner->username }}"
                       class="text-accent-400 hover:text-accent-300 hover:underline font-medium">{{ $thread->lastReply->owner->username }}</a>
                </div>
                <x-avatar size="6" :avatar-path="$thread->lastReply->owner->avatar_path" class="ring-2 ring-steel-700"/>
            @else
                <div class="text-steel-300">
                    <a href="/profiles/{{ $thread->owner->username }}" class="text-accent-400 hover:text-accent-300">{{ $thread->owner->username }}</a>
                    <span>{{ \Carbon\Carbon::parse($thread->created_at)->setTimezone((auth()->check() ? auth()->user()->timezone : null))->diffForHumans() }}</span>
                </div>
                <x-avatar size="8" :avatar-path="$thread->owner->avatar_path" class="ring-2 ring-steel-700"/>
            @endif
        </div>

    </div>

    <div class="hidden md:flex flex-wrap text-sm md:mt-2 items-center">
        <x-thread.forum-tag :$thread/>
        <x-thread.pagination :$thread/>
        <div class="md:flex-1 md:order-2"></div>
        <x-thread.stats :$thread/>
    </div>

    <div class="md:hidden text-xs mt-2">
        <div class="flex items-center">
            <x-thread.forum-tag :$thread/>
            <x-thread.stats :$thread/>
        </div>
        <div class="h-3 md:hidden"></div>
        <x-thread.pagination :$thread/>
    </div>

</article>
