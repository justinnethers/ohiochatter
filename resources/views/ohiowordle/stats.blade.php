<x-app-layout :seo="$seo ?? null">
    <x-slot name="title">Your OhioWordle Stats</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-red-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">Your OhioWordle Stats</h1>
                    <p class="text-steel-400 text-sm">Track your progress and achievements</p>
                </div>
            </div>
            <x-nav-link href="{{ route('ohiowordle.index') }}">
                ← Back to Game
            </x-nav-link>
        </div>
    </x-slot>

    <div class="container mx-auto px-2 md:px-0">
        <div class="space-y-6">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Performance Card -->
                <article class="bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl border border-steel-700/50 overflow-hidden">
                    <div class="h-1 bg-gradient-to-r from-red-600 to-red-500"></div>
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            Performance
                        </h2>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="text-center p-4 bg-gradient-to-br from-steel-700 to-steel-800 rounded-xl border border-steel-600/30">
                                <div class="text-3xl font-bold text-white">{{ $userStats->games_played }}</div>
                                <div class="text-sm text-steel-400">Games Played</div>
                            </div>
                            <div class="text-center p-4 bg-gradient-to-br from-steel-700 to-steel-800 rounded-xl border border-steel-600/30">
                                <div class="text-3xl font-bold text-white">{{ $userStats->games_won }}</div>
                                <div class="text-sm text-steel-400">Games Won</div>
                            </div>
                            <div class="text-center p-4 bg-gradient-to-br from-steel-700 to-steel-800 rounded-xl border border-steel-600/30">
                                <div class="text-3xl font-bold text-white">{{ $userStats->games_played ? round(($userStats->games_won / $userStats->games_played) * 100) : 0 }}%</div>
                                <div class="text-sm text-steel-400">Win Rate</div>
                            </div>
                            <div class="text-center p-4 bg-gradient-to-br from-red-500/20 to-red-600/10 rounded-xl border border-red-500/30">
                                <div class="text-3xl font-bold text-red-400">{{ $userStats->max_streak }}</div>
                                <div class="text-sm text-red-300/70">Best Streak</div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <h3 class="font-semibold text-steel-300 mb-2">Current Streak</h3>
                            <div class="bg-steel-700/50 rounded-full h-4 overflow-hidden">
                                <div
                                    class="bg-gradient-to-r from-red-600 to-red-500 h-4 rounded-full transition-all duration-500"
                                    style="width: {{ min(100, ($userStats->current_streak / max(1, $userStats->max_streak)) * 100) }}%"
                                ></div>
                            </div>
                            <div class="flex justify-between text-sm mt-2 text-steel-400">
                                <span>0</span>
                                <span class="font-medium text-white">{{ $userStats->current_streak }}</span>
                                <span>{{ $userStats->max_streak }}</span>
                            </div>
                        </div>
                    </div>
                </article>

                <!-- Guess Distribution Card -->
                <article class="bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl border border-steel-700/50 overflow-hidden">
                    <div class="h-1 bg-gradient-to-r from-red-600 to-red-500"></div>
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Guess Distribution
                        </h2>
                        @if($userStats->guess_distribution && count($userStats->guess_distribution) > 0)
                            <div class="space-y-3">
                                @foreach(range(1, 6) as $guessNumber)
                                    @php
                                        $count = $userStats->guess_distribution[$guessNumber] ?? 0;
                                        $totalWins = array_sum($userStats->guess_distribution);
                                        $percentage = $totalWins > 0 ? ($count / $totalWins) * 100 : 0;
                                    @endphp
                                    <div class="flex items-center gap-2">
                                        <div class="w-4 text-steel-400 text-sm font-medium">{{ $guessNumber }}</div>
                                        <div class="flex-1">
                                            <div
                                                class="bg-gradient-to-r from-red-600 to-red-500 text-right px-2 py-1 text-sm font-bold rounded text-white"
                                                style="width: max(28px, {{ $percentage }}%)"
                                            >
                                                {{ $count }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-6 pt-4 border-t border-steel-700/50">
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="text-center p-3 bg-steel-700/30 rounded-lg">
                                        <div class="text-xl font-bold text-white">
                                            @php
                                                $mostCommonGuess = array_search(max($userStats->guess_distribution), $userStats->guess_distribution);
                                            @endphp
                                            {{ $mostCommonGuess ?: 'N/A' }}
                                        </div>
                                        <div class="text-xs text-steel-400">Most Common</div>
                                    </div>
                                    <div class="text-center p-3 bg-steel-700/30 rounded-lg">
                                        <div class="text-xl font-bold text-white">
                                            @php
                                                $total = 0;
                                                $count = 0;
                                                foreach ($userStats->guess_distribution as $guess => $num) {
                                                    $total += $guess * $num;
                                                    $count += $num;
                                                }
                                                $average = $count > 0 ? round($total / $count, 1) : 'N/A';
                                            @endphp
                                            {{ $average }}
                                        </div>
                                        <div class="text-xs text-steel-400">Avg Guesses</div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="py-8 text-center text-steel-400">
                                <svg class="w-12 h-12 mx-auto mb-3 text-steel-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                <p>You haven't won any games yet.</p>
                                <p class="text-sm mt-2">Win some games to see your distribution!</p>
                            </div>
                        @endif
                    </div>
                </article>

                <!-- Achievements Card -->
                <article class="bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl border border-steel-700/50 overflow-hidden">
                    <div class="h-1 bg-gradient-to-r from-red-600 to-red-500"></div>
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                            </svg>
                            Achievements
                        </h2>
                        <div class="grid grid-cols-2 gap-3">
                            @php $firstTryEarned = isset($userStats->guess_distribution[1]) && $userStats->guess_distribution[1] > 0; @endphp
                            <div class="{{ $firstTryEarned ? 'bg-gradient-to-br from-red-500/10 to-red-600/5 border-red-500/30 ring-2 ring-red-400/20' : 'bg-steel-800/50 border-steel-700/30 opacity-50' }} rounded-xl p-4 text-center border">
                                <div class="w-12 h-12 {{ $firstTryEarned ? 'bg-red-600 shadow-lg shadow-red-500/30' : 'bg-steel-700' }} rounded-full mx-auto mb-3 flex items-center justify-center">
                                    <svg class="w-6 h-6 {{ $firstTryEarned ? 'text-white' : 'text-steel-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <h3 class="font-semibold {{ $firstTryEarned ? 'text-white' : 'text-steel-400' }}">First Try</h3>
                                <p class="text-xs {{ $firstTryEarned ? 'text-red-300/70' : 'text-steel-500' }} mt-1">Win on first guess</p>
                            </div>

                            @php $onFireEarned = $userStats->current_streak >= 3; @endphp
                            <div class="{{ $onFireEarned ? 'bg-gradient-to-br from-red-500/10 to-red-600/5 border-red-500/30 ring-2 ring-red-400/20' : 'bg-steel-800/50 border-steel-700/30 opacity-50' }} rounded-xl p-4 text-center border">
                                <div class="w-12 h-12 {{ $onFireEarned ? 'bg-red-600 shadow-lg shadow-red-500/30' : 'bg-steel-700' }} rounded-full mx-auto mb-3 flex items-center justify-center">
                                    <svg class="w-6 h-6 {{ $onFireEarned ? 'text-white' : 'text-steel-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </div>
                                <h3 class="font-semibold {{ $onFireEarned ? 'text-white' : 'text-steel-400' }}">On Fire</h3>
                                <p class="text-xs {{ $onFireEarned ? 'text-red-300/70' : 'text-steel-500' }} mt-1">Get a 3-day streak</p>
                            </div>

                            @php $faithfulEarned = $userStats->max_streak >= 10; @endphp
                            <div class="{{ $faithfulEarned ? 'bg-gradient-to-br from-red-500/10 to-red-600/5 border-red-500/30 ring-2 ring-red-400/20' : 'bg-steel-800/50 border-steel-700/30 opacity-50' }} rounded-xl p-4 text-center border">
                                <div class="w-12 h-12 {{ $faithfulEarned ? 'bg-red-600 shadow-lg shadow-red-500/30' : 'bg-steel-700' }} rounded-full mx-auto mb-3 flex items-center justify-center">
                                    <svg class="w-6 h-6 {{ $faithfulEarned ? 'text-white' : 'text-steel-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <h3 class="font-semibold {{ $faithfulEarned ? 'text-white' : 'text-steel-400' }}">Buckeye Faithful</h3>
                                <p class="text-xs {{ $faithfulEarned ? 'text-red-300/70' : 'text-steel-500' }} mt-1">10-day streak</p>
                            </div>

                            @php $masterEarned = $userStats->games_won >= 100; @endphp
                            <div class="{{ $masterEarned ? 'bg-gradient-to-br from-red-500/10 to-red-600/5 border-red-500/30 ring-2 ring-red-400/20' : 'bg-steel-800/50 border-steel-700/30 opacity-50' }} rounded-xl p-4 text-center border">
                                <div class="w-12 h-12 {{ $masterEarned ? 'bg-red-600 shadow-lg shadow-red-500/30' : 'bg-steel-700' }} rounded-full mx-auto mb-3 flex items-center justify-center">
                                    <svg class="w-6 h-6 {{ $masterEarned ? 'text-white' : 'text-steel-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                    </svg>
                                </div>
                                <h3 class="font-semibold {{ $masterEarned ? 'text-white' : 'text-steel-400' }}">Word Master</h3>
                                <p class="text-xs {{ $masterEarned ? 'text-red-300/70' : 'text-steel-500' }} mt-1">Win 100 games</p>
                            </div>
                        </div>
                    </div>
                </article>
            </div>

            <!-- Past Words History -->
            <article class="bg-gradient-to-br from-steel-800/80 to-steel-900/80 rounded-xl border border-steel-700/30 overflow-hidden">
                <div class="p-6 border-b border-steel-700/30">
                    <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Past Words
                    </h2>
                </div>
                <div class="p-6">
                    @php
                        $pastWords = $recentWords->filter(function($word) use ($wordProgress) {
                            if ($word->publish_date->isToday()) {
                                return isset($wordProgress[$word->id]) &&
                                       $wordProgress[$word->id] &&
                                       $wordProgress[$word->id]->completed_at;
                            }
                            return $word->publish_date->isPast() &&
                                   isset($wordProgress[$word->id]) &&
                                   $wordProgress[$word->id];
                        });
                    @endphp

                    @if(count($pastWords) > 0)
                        <div class="space-y-4">
                            @foreach($pastWords as $word)
                                @php
                                    $progress = $wordProgress[$word->id] ?? null;
                                @endphp

                                @if($progress)
                                    <div class="bg-gradient-to-br from-steel-700/50 to-steel-800/50 rounded-xl p-4 border border-steel-600/30">
                                        <div class="flex justify-between items-start mb-3">
                                            <div>
                                                <h3 class="text-xl font-bold text-amber-400">{{ $word->word }}</h3>
                                                <p class="text-steel-400 text-sm">{{ $word->publish_date->format('F j, Y') }}</p>
                                            </div>
                                            @if($progress->solved)
                                                <span class="px-3 py-1 bg-green-500/20 text-green-400 text-xs font-medium rounded-full border border-green-500/30">
                                                    Solved ({{ $progress->guesses_taken }}/6)
                                                </span>
                                            @else
                                                <span class="px-3 py-1 bg-red-500/20 text-red-400 text-xs font-medium rounded-full border border-red-500/30">
                                                    Failed
                                                </span>
                                            @endif
                                        </div>

                                        @if($progress->guesses && count($progress->guesses) > 0)
                                            <div>
                                                <h4 class="text-sm font-medium text-steel-400 mb-2">Your Guesses:</h4>
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($progress->guesses as $index => $guess)
                                                        <div class="flex gap-px">
                                                            @foreach(str_split($guess) as $letterIndex => $letter)
                                                                @php
                                                                    $feedback = $progress->feedback[$index][$letterIndex] ?? 'absent';
                                                                    $bgColor = match($feedback) {
                                                                        'correct' => 'bg-red-600',
                                                                        'present' => 'bg-gray-400',
                                                                        default => 'bg-steel-600',
                                                                    };
                                                                @endphp
                                                                <span class="w-7 h-7 text-xs font-bold flex items-center justify-center rounded {{ $bgColor }} text-white">
                                                                    {{ strtoupper($letter) }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        @if($word->hint)
                                            <div class="mt-3 text-sm text-steel-400">
                                                <span class="text-steel-500">Hint:</span> {{ $word->hint }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="py-12 text-center text-steel-400">
                            <svg class="w-16 h-16 mx-auto mb-4 text-steel-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-lg">You haven't completed any past puzzles yet.</p>
                            <p class="text-sm mt-2">Play today's puzzle to see your history here!</p>
                            <a href="{{ route('ohiowordle.index') }}" class="inline-block mt-4">
                                <x-primary-button>Play Today's Puzzle</x-primary-button>
                            </a>
                        </div>
                    @endif
                </div>
            </article>
        </div>
    </div>
</x-app-layout>
