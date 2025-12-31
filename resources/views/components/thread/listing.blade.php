@php
    $isOwner = auth()->check() && $thread->owner && auth()->id() === $thread->owner->id;
    $hasReplied = auth()->check() && auth()->user()->hasRepliedTo($thread);
    $hasParticipated = $isOwner || $hasReplied;
@endphp
<article class="group {{ $thread->locked ? 'bg-steel-850/80 border-steel-700/30' : 'bg-gradient-to-br from-steel-800 to-steel-850 border-steel-700/50 hover:border-steel-600 hover:shadow-xl hover:-translate-y-0.5' }} p-3 md:p-4 text-steel-100 font-body rounded-xl mb-2 md:mb-5 shadow-lg shadow-black/20 border transition-all duration-300 relative overflow-hidden">
    {{-- Locked state --}}
    @if ($thread->locked)
        <div class="absolute inset-0 bg-gradient-to-br from-steel-900/30 to-steel-950/40 pointer-events-none"></div>
        <div class="absolute top-3 right-3 flex items-center gap-1.5 text-steel-500 text-xs font-medium bg-steel-900/60 px-2 py-1 rounded-full">
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
            </svg>
            <span>Locked</span>
        </div>
    @endif

    {{-- Left edge indicator --}}
    @if ($thread->locked)
        <div class="absolute left-0 top-0 bottom-0 w-1 bg-steel-600/50"></div>
    @elseif ($hasParticipated)
        <div class="absolute left-0 top-4 bottom-4 w-1 bg-accent-500 rounded-r-full"></div>
    @endif

    <a class="group/title text-lg md:text-xl font-semibold transition-colors duration-200 block leading-snug" href="/forums/{{ $thread->forum->slug }}/{{ $thread->slug }}">
        @if (auth()->check() && $thread->hasUpdatesFor(auth()->user()))
            <span class="font-bold {{ $thread->locked ? 'text-steel-400 group-hover/title:text-steel-300' : 'text-white group-hover/title:text-accent-400' }} transition-colors duration-200 inline-flex items-center">
                <span class="w-2.5 h-2.5 bg-accent-400 rounded-full mr-2.5 flex-shrink-0"></span>
                @if ($thread->poll)
                    <span class="text-amber-400 mr-1">Poll:</span>
                @endif{{ $thread->title }}
            </span>
        @else
            <span class="font-normal {{ $thread->locked ? 'text-steel-500 group-hover/title:text-steel-400' : 'text-steel-300 group-hover/title:text-accent-400' }} transition-colors duration-200">
                @if ($thread->poll)
                    <span class="text-amber-400/70 mr-1">Poll:</span>
                @endif{{ $thread->title }}
            </span>
        @endif
    </a>

    {{-- Mobile: Show latest reply info (or thread creator if no replies) --}}
    <div class="md:hidden flex items-center gap-2 mt-1.5 text-xs text-steel-400">
        @if ($thread->lastReply)
            <x-avatar size="5" :avatar-path="$thread->lastReply->owner->avatar_path" class="ring-1 ring-steel-700"/>
            <a href="/profiles/{{ $thread->lastReply->owner->username }}" class="text-accent-400 hover:text-accent-300 font-medium">{{ $thread->lastReply->owner->username }}</a>
            <span>&middot;</span>
            <span>{{ \Carbon\Carbon::parse($thread->lastReply->created_at)->setTimezone((auth()->check() ? auth()->user()->timezone : null))->diffForHumans() }}</span>
        @elseif ($thread->owner)
            <x-avatar size="5" :avatar-path="$thread->owner->avatar_path" class="ring-1 ring-steel-700"/>
            <a href="/profiles/{{ $thread->owner->username }}" class="text-accent-400 hover:text-accent-300 font-medium">{{ $thread->owner->username }}</a>
            <span>&middot;</span>
            <span>{{ \Carbon\Carbon::parse($thread->created_at)->setTimezone((auth()->check() ? auth()->user()->timezone : null))->diffForHumans() }}</span>
        @endif
    </div>

    {{-- Desktop: Full metadata box --}}
    <div class="hidden md:flex text-base justify-between rounded-lg my-3 bg-steel-900/50 shadow-inner">
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

        <div class="flex items-center justify-end space-x-2 py-2 px-3">
            @if ($thread->lastReply)
                <div class="text-right text-steel-300">
                    <span class="mr-1">
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

    <div class="md:hidden text-xs mt-2 flex items-center gap-2">
        <x-thread.forum-tag :$thread/>
        <x-thread.stats :$thread/>
        <div class="flex-1"></div>
        <x-thread.pagination :$thread/>
    </div>

</article>
