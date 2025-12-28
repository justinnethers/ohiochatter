@props(['threads', 'repScore'])

<div class="mt-6 md:rounded-2xl md:bg-gradient-to-br md:from-steel-800/50 md:to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-2 md:p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-white flex items-center gap-2">
            <span class="w-1 h-5 bg-accent-500 rounded-full"></span>
            Your Activity
        </h2>
        <div class="flex items-center gap-3">
            <span class="text-sm @if($repScore >= 0) text-emerald-400 @else text-red-400 @endif font-medium">
                Rep: {{ $repScore >= 0 ? '+' : '' }}{{ number_format($repScore) }}
            </span>
            <a href="{{ route('dashboard') }}" class="text-sm text-accent-400 hover:text-accent-300">
                Dashboard &rarr;
            </a>
        </div>
    </div>

    <div class="space-y-2">
        <p class="text-sm text-steel-400 mb-2">Threads with new replies:</p>
        @foreach($threads as $thread)
            <a href="{{ $thread->path() }}"
               class="flex items-center gap-3 p-3 bg-gradient-to-br from-steel-800 to-steel-850 rounded-lg border border-steel-700/50 hover:border-steel-600 transition-all group">
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-steel-200 group-hover:text-white truncate">
                        {{ $thread->title }}
                    </div>
                    @if($thread->lastReply)
                        <div class="text-xs text-steel-500 mt-0.5">
                            Latest reply by
                            <span class="text-accent-400">{{ $thread->lastReply->owner->username }}</span>
                            {{ $thread->lastReply->created_at->diffForHumans() }}
                        </div>
                    @endif
                </div>
                <div class="shrink-0">
                    @if($thread->lastReply)
                        <x-avatar size="6" :avatar-path="$thread->lastReply->owner->avatar_path" class="ring-1 ring-steel-700"/>
                    @endif
                </div>
            </a>
        @endforeach
    </div>
</div>
