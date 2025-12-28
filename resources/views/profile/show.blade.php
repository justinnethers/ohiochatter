<x-app-layout>
    <x-slot name="title">{{ $user->username }}'s Profile</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-white leading-tight flex items-center gap-3">
                <span class="hidden md:inline-block w-1 h-6 bg-accent-500 rounded-full"></span>
                {{ $user->username }}'s Profile
            </h2>
            <a href="{{ url()->previous() }}" class="text-steel-300 hover:text-white transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back
            </a>
        </div>
    </x-slot>

    <div class="container mx-auto">
        <div class="md:rounded-2xl md:bg-gradient-to-br md:from-steel-800/50 md:to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-2 md:p-8 md:mt-4">

            {{-- Profile Header Card --}}
            <div class="bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50 overflow-hidden mb-6">
                {{-- Generated pattern banner --}}
                <div class="h-24 md:h-32 relative overflow-hidden">
                    <x-profile-pattern :username="$user->username" class="absolute inset-0 w-full h-full" />
                </div>

                <div class="px-4 md:px-8 pb-6 -mt-12 md:-mt-16">
                    <div class="flex flex-col md:flex-row md:items-end gap-4">
                        {{-- Avatar --}}
                        <div class="relative">
                            <x-avatar size="24" :avatar-path="$user->avatar_path" class="ring-4 ring-steel-800 md:w-32 md:h-32"/>
                            @if($user->is_admin)
                                <span class="absolute -bottom-1 -right-1 bg-gradient-to-r from-amber-500 to-amber-600 text-white text-xs font-bold px-2 py-0.5 rounded-full shadow-lg">
                                    Admin
                                </span>
                            @elseif($user->is_moderator)
                                <span class="absolute -bottom-1 -right-1 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white text-xs font-bold px-2 py-0.5 rounded-full shadow-lg">
                                    Mod
                                </span>
                            @endif
                        </div>

                        {{-- User info --}}
                        <div class="flex-1">
                            <h1 class="text-2xl md:text-3xl font-bold text-white">{{ $user->username }}</h1>
                            <p class="text-accent-400 font-medium">{{ $user->usertitle }}</p>
                        </div>

                        {{-- Quick actions --}}
                        @auth
                            @if(auth()->id() !== $user->id)
                                <a href="{{ route('messages.create') }}?recipient={{ $user->username }}"
                                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-accent-500 to-accent-600 rounded-lg text-white font-semibold text-sm shadow-lg shadow-accent-500/25 hover:shadow-accent-500/40 hover:from-accent-600 hover:to-accent-700 transition-all duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                    Message
                                </a>
                            @else
                                <a href="{{ route('profile.edit') }}"
                                   class="inline-flex items-center px-4 py-2 bg-steel-700 hover:bg-steel-600 rounded-lg text-white font-medium text-sm transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Edit Profile
                                </a>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Left column: Stats --}}
                <div class="space-y-6">
                    {{-- Member Stats Card --}}
                    <div class="bg-gradient-to-br from-steel-800 to-steel-850 p-6 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50">
                        <h3 class="text-lg font-semibold text-white mb-4">Member Stats</h3>

                        <dl class="space-y-3">
                            <div class="flex justify-between items-center py-2 border-b border-steel-700/50">
                                <dt class="text-steel-400">Member Since</dt>
                                <dd class="text-white font-medium">{{ $joinDate->format('M j, Y') }}</dd>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-steel-700/50">
                                <dt class="text-steel-400">Account Age</dt>
                                <dd class="text-white font-medium">{{ $accountAge }}</dd>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-steel-700/50">
                                <dt class="text-steel-400">Total Posts</dt>
                                <dd class="text-white font-medium">{{ number_format($user->posts_count) }}</dd>
                            </div>
                            @if($user->last_activity)
                                <div class="flex justify-between items-center py-2 border-b border-steel-700/50">
                                    <dt class="text-steel-400">Last Active</dt>
                                    <dd class="text-white font-medium">{{ $user->last_activity->diffForHumans() }}</dd>
                                </div>
                            @endif
                            <div class="flex justify-between items-center py-2">
                                <dt class="text-steel-400">Last Post</dt>
                                <dd class="text-white font-medium">
                                    @if($lastPostDate)
                                        {{ $lastPostDate->diffForHumans() }}
                                    @else
                                        Never
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Reputation Card --}}
                    <div class="bg-gradient-to-br from-steel-800 to-steel-850 p-6 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50">
                        <h3 class="text-lg font-semibold text-white mb-4">Reputation</h3>

                        {{-- Reputation Score --}}
                        @php $repScore = $totalReps - $totalNegs; @endphp
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

                    {{-- BuckEYE Game Stats --}}
                    @if($gameStats && $gameStats->games_played > 0)
                        <div class="bg-gradient-to-br from-steel-800 to-steel-850 p-6 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50">
                            <h3 class="text-lg font-semibold text-white mb-4">BuckEYE Stats</h3>

                            <dl class="space-y-3">
                                <div class="flex justify-between items-center py-2 border-b border-steel-700/50">
                                    <dt class="text-steel-400">Games Played</dt>
                                    <dd class="text-white font-medium">{{ number_format($gameStats->games_played) }}</dd>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-steel-700/50">
                                    <dt class="text-steel-400">Win Rate</dt>
                                    <dd class="text-emerald-400 font-medium">
                                        {{ $gameStats->games_played > 0 ? number_format(($gameStats->games_won / $gameStats->games_played) * 100, 1) : 0 }}%
                                    </dd>
                                </div>
                                <div class="flex justify-between items-center py-2 border-b border-steel-700/50">
                                    <dt class="text-steel-400">Current Streak</dt>
                                    <dd class="text-amber-400 font-medium flex items-center gap-1">
                                        {{ $gameStats->current_streak }}
                                        @if($gameStats->current_streak >= 5)
                                            <span class="text-lg">🔥</span>
                                        @endif
                                    </dd>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <dt class="text-steel-400">Best Streak</dt>
                                    <dd class="text-white font-medium">{{ $gameStats->max_streak }}</dd>
                                </div>
                            </dl>

                            {{-- Guess Distribution --}}
                            @if($gameStats->guess_distribution && count($gameStats->guess_distribution) > 0)
                                <div class="mt-4 pt-4 border-t border-steel-700/50">
                                    <h4 class="text-sm font-semibold text-steel-300 mb-3">Guess Distribution</h4>
                                    @php
                                        $maxGuesses = max($gameStats->guess_distribution);
                                    @endphp
                                    <div class="space-y-2">
                                        @for($i = 1; $i <= 5; $i++)
                                            @php
                                                $count = $gameStats->guess_distribution[$i] ?? 0;
                                                $percentage = $maxGuesses > 0 ? ($count / $maxGuesses) * 100 : 0;
                                            @endphp
                                            <div class="flex items-center gap-2">
                                                <span class="w-4 text-steel-400 text-sm">{{ $i }}</span>
                                                <div class="flex-1 h-5 bg-steel-900 rounded overflow-hidden">
                                                    <div class="h-full bg-accent-500 rounded flex items-center justify-end px-2 text-xs font-medium text-white min-w-[2rem]"
                                                         style="width: {{ max($percentage, 10) }}%">
                                                        {{ $count }}
                                                    </div>
                                                </div>
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Right column: Activity --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Recent Threads --}}
                    @if($threads->count() > 0)
                        <div class="bg-gradient-to-br from-steel-800 to-steel-850 p-6 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50">
                            <h3 class="text-lg font-semibold text-white mb-4">Recent Threads</h3>

                            <div class="space-y-4">
                                @foreach($threads as $thread)
                                    <x-thread.card :thread="$thread" :reply-count="$thread->replyCount()" />
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Recent Posts --}}
                    <div class="bg-gradient-to-br from-steel-800 to-steel-850 p-6 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50">
                        <h3 class="text-lg font-semibold text-white mb-4">Recent Posts</h3>

                        @if($recentPosts->count() > 0)
                            <div class="space-y-4">
                                @foreach($recentPosts as $post)
                                    @if($post->thread)
                                        <x-thread.card
                                            :thread="$post->thread"
                                            :href="$post->thread->path() . '#reply-' . $post->id"
                                            :timestamp="$post->created_at"
                                            :excerpt="Str::limit(strip_tags($post->body), 200)"
                                        />
                                    @else
                                        <div class="p-4 rounded-lg bg-steel-900/50 border border-steel-700/30">
                                            <span class="font-medium text-steel-500 italic">Deleted thread</span>
                                            <div class="mt-2 text-steel-400 text-sm">{{ $post->created_at->diffForHumans() }}</div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 text-steel-400">
                                No posts yet.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
