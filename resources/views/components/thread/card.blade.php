@props([
    'thread',
    'href' => null,
    'timestamp' => null,
    'replyCount' => null,
    'excerpt' => null,
])

@php
    $url = $href ?? $thread->path();
    $time = $timestamp ?? $thread->created_at;
    $forum = $thread->forum;
@endphp

<a href="{{ $url }}" class="group block p-4 rounded-xl bg-steel-900/50 border border-steel-700/30 hover:border-steel-600 hover:bg-steel-900 transition-all duration-200">
    <span class="text-lg text-white font-semibold group-hover:text-accent-400 transition-colors block mb-3">
        {{ $thread->title }}
    </span>

    <span class="flex items-center justify-between flex-wrap gap-2">
        <span class="flex items-center gap-3">
            <span class="inline-flex items-center px-3 py-1 bg-{{ $forum->color ?? 'steel' }}-500 rounded-full text-sm font-semibold text-white shadow-lg shadow-black/20">
                {{ $forum->name }}
            </span>
            <span class="text-sm text-steel-400">{{ $time->diffForHumans() }}</span>
        </span>

        @if($replyCount !== null)
            <span class="inline-flex items-center gap-2 px-3 py-1 bg-steel-900/70 rounded-full border border-steel-700/50">
                <svg class="w-4 h-4 text-steel-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zm-4 0H9v2h2V9z" clip-rule="evenodd"/>
                </svg>
                <span class="font-bold text-steel-100">{{ number_format($replyCount) }}</span>
            </span>
        @endif
    </span>

    @if($excerpt)
        <span class="mt-3 text-steel-300 text-sm line-clamp-2 block post-body whitespace-pre-line">
            {{ $excerpt }}
        </span>
    @endif
</a>
