<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-white leading-tight flex items-center gap-3">
                <span class="hidden md:inline-block w-1 h-6 bg-accent-500 rounded-full"></span>
                Dashboard
            </h2>
            <a href="{{ route('thread.index') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-accent-500 to-accent-600 rounded-lg text-white font-semibold text-sm shadow-lg shadow-accent-500/25 hover:shadow-accent-500/40 hover:from-accent-600 hover:to-accent-700 transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Thread
            </a>
        </div>
    </x-slot>

    <div class="container mx-auto">
        <div class="md:rounded-2xl md:bg-gradient-to-br md:from-steel-800/50 md:to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-2 md:p-8 md:mt-4">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Left column: Quick Stats --}}
                <div class="space-y-6">
                    {{-- Welcome Card --}}
                    <div class="bg-gradient-to-br from-steel-800 to-steel-850 p-6 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50">
                        <div class="flex items-center gap-4">
                            <x-avatar size="16" :avatar-path="$user->avatar_path" class="ring-2 ring-accent-500/50"/>
                            <div>
                                <h2 class="text-xl font-bold text-white">{{ $user->username }}</h2>
                                <a href="{{ route('profile.show', $user) }}" class="text-accent-400 hover:text-accent-300 text-sm transition-colors">
                                    View Profile
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Reputation Score --}}
                    <div class="bg-gradient-to-br from-steel-800 to-steel-850 p-6 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50">
                        <h3 class="text-lg font-semibold text-white mb-4">Reputation</h3>

                        <div class="text-center py-4 mb-4 rounded-lg bg-steel-900/50">
                            <div class="text-4xl font-bold {{ $repScore >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                {{ $repScore >= 0 ? '+' : '' }}{{ number_format($repScore) }}
                            </div>
                            <div class="text-steel-400 text-sm mt-1">Reputation Score</div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center p-3 rounded-lg bg-emerald-500/10 border border-emerald-500/20">
                                <div class="text-2xl font-bold text-emerald-400">{{ number_format($totalReps) }}</div>
                                <div class="text-emerald-400/70 text-sm flex items-center justify-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Reps
                                </div>
                            </div>
                            <div class="text-center p-3 rounded-lg bg-red-500/10 border border-red-500/20">
                                <div class="text-2xl font-bold text-red-400">{{ number_format($totalNegs) }}</div>
                                <div class="text-red-400/70 text-sm flex items-center justify-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 10.293a1 1 0 010 1.414l-6 6a1 1 0 01-1.414 0l-6-6a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l4.293-4.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    Negs
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Messages --}}
                    <div class="bg-gradient-to-br from-steel-800 to-steel-850 p-6 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-white">Messages</h3>
                            @if($unreadMessageCount > 0)
                                <span class="px-2 py-1 bg-accent-500 text-white text-xs font-bold rounded-full">
                                    {{ $unreadMessageCount }} new
                                </span>
                            @endif
                        </div>

                        @if($messageThreads->count() > 0)
                            <div class="space-y-3">
                                @foreach($messageThreads->take(3) as $thread)
                                    <a href="{{ route('messages.show', $thread) }}"
                                       class="block p-3 rounded-lg bg-steel-900/50 border border-steel-700/30 hover:border-steel-600 hover:bg-steel-900 transition-all duration-200 group">
                                        <div class="font-medium text-white group-hover:text-accent-400 transition-colors text-sm truncate">
                                            {{ $thread->subject }}
                                        </div>
                                        <div class="text-steel-400 text-xs mt-1">
                                            {{ $thread->updated_at->diffForHumans() }}
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                            <a href="{{ route('messages.index') }}" class="block mt-4 text-center text-accent-400 hover:text-accent-300 text-sm transition-colors">
                                View all messages
                            </a>
                        @else
                            <div class="text-center py-4 text-steel-400 text-sm">
                                No messages yet.
                            </div>
                        @endif
                    </div>

                    {{-- BuckEYE Game Stats --}}
                    @if($gameStats && $gameStats->games_played > 0)
                        <div class="bg-gradient-to-br from-steel-800 to-steel-850 p-6 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-white">BuckEYE Stats</h3>
                                <a href="{{ route('buckeye.index') }}" class="text-accent-400 hover:text-accent-300 text-sm transition-colors">
                                    Play
                                </a>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div class="text-center p-3 rounded-lg bg-steel-900/50">
                                    <div class="text-2xl font-bold text-white">{{ $gameStats->games_won }}</div>
                                    <div class="text-steel-400 text-xs">Wins</div>
                                </div>
                                <div class="text-center p-3 rounded-lg bg-steel-900/50">
                                    <div class="text-2xl font-bold text-amber-400 flex items-center justify-center gap-1">
                                        {{ $gameStats->current_streak }}
                                        @if($gameStats->current_streak >= 5)
                                            <span class="text-lg">🔥</span>
                                        @endif
                                    </div>
                                    <div class="text-steel-400 text-xs">Streak</div>
                                </div>
                            </div>

                            <div class="mt-4 pt-4 border-t border-steel-700/50 flex justify-between text-sm">
                                <span class="text-steel-400">Win Rate</span>
                                <span class="text-emerald-400 font-medium">
                                    {{ number_format(($gameStats->games_won / $gameStats->games_played) * 100, 1) }}%
                                </span>
                            </div>
                        </div>
                    @else
                        <div class="bg-gradient-to-br from-steel-800 to-steel-850 p-6 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50">
                            <h3 class="text-lg font-semibold text-white mb-4">BuckEYE Game</h3>
                            <p class="text-steel-400 text-sm mb-4">Play Ohio's daily puzzle game!</p>
                            <a href="{{ route('buckeye.index') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-amber-500 to-amber-600 rounded-lg text-white font-semibold text-sm shadow-lg hover:from-amber-600 hover:to-amber-700 transition-all duration-200">
                                Play Now
                            </a>
                        </div>
                    @endif
                </div>

                {{-- Right column: Activity --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Threads with New Activity --}}
                    <div class="bg-gradient-to-br from-steel-800 to-steel-850 p-6 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50">
                        <h3 class="text-lg font-semibold text-white mb-4">New Activity</h3>

                        @if($threadsWithActivity->count() > 0)
                            <div class="space-y-3">
                                @foreach($threadsWithActivity as $thread)
                                    <a href="{{ $thread->path() }}"
                                       class="block p-4 rounded-lg bg-steel-900/50 border border-steel-700/30 hover:border-accent-500/50 hover:bg-steel-900 transition-all duration-200 group">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="flex-1 min-w-0">
                                                <span class="font-medium text-white group-hover:text-accent-400 transition-colors">
                                                    {{ $thread->title }}
                                                </span>
                                                <div class="flex items-center gap-3 mt-2 text-sm text-steel-400">
                                                    <span class="inline-flex items-center px-2 py-0.5 bg-{{ $thread->forum->color ?? 'steel' }}-500/20 text-{{ $thread->forum->color ?? 'steel' }}-400 rounded text-xs font-medium">
                                                        {{ $thread->forum->name }}
                                                    </span>
                                                    @if($thread->lastReply)
                                                        <span>{{ $thread->lastReply->owner->username }} replied {{ $thread->lastReply->created_at->diffForHumans() }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <span class="flex-shrink-0 w-2 h-2 bg-accent-500 rounded-full mt-2"></span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 text-steel-400">
                                <svg class="w-12 h-12 mx-auto mb-3 text-steel-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p>You're all caught up!</p>
                                <p class="text-sm mt-1">No new activity in your threads.</p>
                            </div>
                        @endif
                    </div>

                    {{-- Recent Rep Activity --}}
                    @if($recentRepActivity->count() > 0)
                        <div class="bg-gradient-to-br from-steel-800 to-steel-850 p-6 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50">
                            <h3 class="text-lg font-semibold text-white mb-4">Recent Reputation</h3>

                            <div class="space-y-3">
                                @foreach($recentRepActivity as $activity)
                                    <div class="flex items-center gap-4 p-3 rounded-lg bg-steel-900/50 border border-steel-700/30">
                                        <div class="flex-shrink-0">
                                            @if($activity['type'] === 'rep')
                                                <div class="w-10 h-10 rounded-full bg-emerald-500/20 flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                </div>
                                            @else
                                                <div class="w-10 h-10 rounded-full bg-red-500/20 flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 10.293a1 1 0 010 1.414l-6 6a1 1 0 01-1.414 0l-6-6a1 1 0 111.414-1.414L9 14.586V3a1 1 0 012 0v11.586l4.293-4.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-white text-sm">
                                                <a href="{{ route('profile.show', ['user' => $activity['username']]) }}" class="font-medium hover:text-accent-400 transition-colors">
                                                    {{ $activity['username'] }}
                                                </a>
                                                <span class="text-steel-400">
                                                    gave you a {{ $activity['type'] === 'rep' ? 'rep' : 'neg' }}
                                                </span>
                                            </p>
                                            <a href="{{ route('thread.show', ['forum' => $activity['forum_slug'], 'thread' => $activity['thread_slug']]) }}" class="text-steel-400 text-xs hover:text-accent-400 transition-colors truncate block">
                                                in {{ $activity['thread_title'] }}
                                            </a>
                                        </div>
                                        <div class="text-steel-500 text-xs flex-shrink-0">
                                            {{ \Carbon\Carbon::parse($activity['created_at'])->diffForHumans() }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Your Recent Threads --}}
                    @if($userThreads->count() > 0)
                        <div class="bg-gradient-to-br from-steel-800 to-steel-850 p-6 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50">
                            <h3 class="text-lg font-semibold text-white mb-4">Your Threads</h3>

                            <div class="space-y-4">
                                @foreach($userThreads as $thread)
                                    <x-thread.card :thread="$thread" :reply-count="$thread->replies_count" />
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
