@props(['post'])

@php
    $page = \App\Services\ReplyPaginationService::calculatePage(
        $post->position ?? 1,
        \App\Services\ReplyPaginationService::getPerPage()
    );
    $postUrl = $post->thread->path() . '?page=' . $page . '#reply-' . $post->id;
@endphp

<article class="group bg-gradient-to-br from-steel-800 to-steel-850 border-steel-700/50 hover:border-steel-600 hover:shadow-xl hover:-translate-y-0.5 p-3 md:p-4 text-steel-100 font-body rounded-xl mb-2 md:mb-5 shadow-lg shadow-black/20 border transition-all duration-300 relative overflow-hidden">
    <a class="group/title text-lg md:text-xl font-semibold transition-colors duration-200 block leading-snug" href="{{ $postUrl }}">
        <span class="text-steel-400 font-normal">Reply in</span>
        <span class="text-steel-300 group-hover/title:text-accent-400 transition-colors duration-200 ml-1">{{ $post->thread->title }}</span>
    </a>

    {{-- Mobile: Simple inline metadata --}}
    <div class="md:hidden flex items-center gap-2 mt-1.5 text-xs text-steel-400">
        @if ($post->owner)
            <x-avatar size="5" :avatar-path="$post->owner->avatar_path" class="ring-1 ring-steel-700"/>
            <a href="/profiles/{{ $post->owner->username }}" class="text-accent-400 hover:text-accent-300 font-medium">{{ $post->owner->username }}</a>
            <span>&middot;</span>
            <span>{{ \Carbon\Carbon::parse($post->created_at)->setTimezone(auth()->user()?->timezone ?? config('app.timezone'))->diffForHumans() }}</span>
        @endif
    </div>

    {{-- Desktop: Full metadata box --}}
    <div class="hidden md:flex text-base justify-between rounded-lg my-3 bg-steel-900/50 shadow-inner">
        <div class="flex items-center space-x-2 py-2 px-3">
            @if ($post->owner)
                <x-avatar size="6" :avatar-path="$post->owner->avatar_path" class="ring-2 ring-steel-700"/>
                <span class="text-steel-300">
                    {{ \Carbon\Carbon::parse($post->created_at)->setTimezone(auth()->user()?->timezone ?? config('app.timezone'))->diffForHumans() }}
                    <a href="/profiles/{{ $post->owner->username }}"
                       class="text-accent-400 hover:text-accent-300 hover:underline font-medium">{{ $post->owner->username }}</a>
                </span>
            @endif
        </div>

        <div class="flex items-center justify-end space-x-2 py-2 px-3">
            <x-thread.forum-tag :thread="$post->thread"/>
        </div>
    </div>

    {{-- Body excerpt --}}
    <div class="text-sm text-steel-400 mt-2 md:mt-0">
        {{ Str::limit(strip_tags($post->body), 200) }}
    </div>

    {{-- Mobile: Forum tag --}}
    <div class="md:hidden text-xs mt-2 flex items-center gap-2">
        <x-thread.forum-tag :thread="$post->thread"/>
    </div>
</article>
