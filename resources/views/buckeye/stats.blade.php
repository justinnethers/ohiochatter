<!-- resources/views/buckeye/stats.blade.php -->
<x-app-layout>
    <x-slot name="title">Your BuckEYE Stats</x-slot>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-gray-200 dark:text-gray-200 leading-tight">
                Your BuckEYE Stats
            </h2>
            <x-nav-link
                href="{{ route('buckeye.index') }}"
            >Back to Game
            </x-nav-link>
        </div>
    </x-slot>

    <div class="text-gray-200">
        <div class="md:rounded-lg md:bg-gray-800 p-2 md:p-8 md:pt-4 md:mt-4">
            <div class="max-w-6xl mx-auto space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Overall Stats Card -->
                    <article>
                        <div class="bg-red-600 text-white p-2 px-4 rounded-t-md">
                            <h2 class="text-lg font-semibold">Overall Performance</h2>
                        </div>
                        <div class="p-2 bg-gray-700 rounded-b-md">
                            <div class="bg-gray-800 p-2 rounded-md">
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="text-center p-3 bg-gray-700 rounded-lg">
                                        <div class="text-3xl font-bold">{{ $userStats->games_played }}</div>
                                        <div class="text-sm text-gray-400">Games Played</div>
                                    </div>
                                    <div class="text-center p-3 bg-gray-700 rounded-lg">
                                        <div class="text-3xl font-bold">{{ $userStats->games_won }}</div>
                                        <div class="text-sm text-gray-400">Games Won</div>
                                    </div>
                                    <div class="text-center p-3 bg-gray-700 rounded-lg">
                                        <div
                                            class="text-3xl font-bold">{{ $userStats->games_played ? round(($userStats->games_won / $userStats->games_played) * 100) : 0 }}
                                            %
                                        </div>
                                        <div class="text-sm text-gray-400">Win Rate</div>
                                    </div>
                                    <div class="text-center p-3 bg-green-500 text-green-950 rounded-lg">
                                        <div class="text-3xl font-bold">{{ $userStats->max_streak }}</div>
                                        <div class="text-sm">Best Streak</div>
                                    </div>
                                </div>

                                <div class="mt-6">
                                    <h3 class="font-semibold mb-2">Current Streak</h3>
                                    <div class="bg-gray-600 rounded-full h-4">
                                        <div
                                            class="bg-blue-600 h-4 rounded-full"
                                            style="width: {{ min(100, ($userStats->current_streak / max(1, $userStats->max_streak)) * 100) }}%"
                                        ></div>
                                    </div>
                                    <div class="flex justify-between text-sm mt-1">
                                        <div>0</div>
                                        <div class="font-medium">{{ $userStats->current_streak }}</div>
                                        <div>{{ $userStats->max_streak }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>

                    <!-- Guess Distribution Card -->
                    <article>
                        <div class="bg-blue-600 text-white p-2 px-4 rounded-t-md">
                            <h2 class="text-lg font-semibold">Guess Distribution</h2>
                        </div>
                        <div class="p-2 bg-gray-700 rounded-b-md">
                            <div class="bg-gray-800 p-2 rounded-md">
                                @if($userStats->guess_distribution && count($userStats->guess_distribution) > 0)
                                    <div class="space-y-3">
                                        @foreach(range(1, 5) as $guessNumber)
                                            @php
                                                $count = $userStats->guess_distribution[$guessNumber] ?? 0;
                                                $totalWins = array_sum($userStats->guess_distribution);
                                                $percentage = $totalWins > 0 ? ($count / $totalWins) * 100 : 0;
                                            @endphp
                                            <div class="flex items-center">
                                                <div class="w-4 text-gray-300">{{ $guessNumber }}</div>
                                                <div class="flex-1 ml-2">
                                                    <div
                                                        class="bg-green-500 text-green-950 text-right px-2 py-1 text-sm font-bold rounded-sm"
                                                        style="width: {{ max(5, $percentage) }}%"
                                                    >
                                                        {{ $count }}
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="mt-4 pt-4 border-t border-gray-600">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="text-center p-2 bg-gray-700 rounded-lg">
                                                <div class="text-2xl font-bold">
                                                    @php
                                                        $mostCommonGuess = array_search(max($userStats->guess_distribution), $userStats->guess_distribution);
                                                    @endphp
                                                    {{ $mostCommonGuess ?: 'N/A' }}
                                                </div>
                                                <div class="text-xs text-gray-400">Most Common</div>
                                            </div>
                                            <div class="text-center p-2 bg-gray-700 rounded-lg">
                                                <div class="text-2xl font-bold">
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
                                                <div class="text-xs text-gray-400">Average Guesses</div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="py-8 text-center text-gray-400">
                                        <p>You haven't won any games yet.</p>
                                        <p class="text-sm mt-2">Win some games to see your guess distribution!</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </article>

                    <!-- Achievements Card -->
                    <article>
                        <div class="bg-purple-600 text-white p-2 px-4 rounded-t-md">
                            <h2 class="text-lg font-semibold">Achievements</h2>
                        </div>
                        <div class="p-2 bg-gray-700 rounded-b-md">
                            <div class="bg-gray-800 p-2 rounded-md">
                                <div class="grid grid-cols-2 gap-2">
                                    <div
                                        class="bg-gray-700 rounded-lg p-2 text-center {{ isset($userStats->guess_distribution[1]) && $userStats->guess_distribution[1] > 0 ? 'opacity-100' : 'opacity-40' }}">
                                        <div
                                            class="w-12 h-12 bg-gray-600 rounded-full mx-auto mb-3 flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-300"
                                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <h3 class="font-medium">Eagle Eye</h3>
                                        <p class="text-xs text-gray-400 mt-1">Win on first guess</p>
                                    </div>

                                    <div
                                        class="bg-gray-700 rounded-lg p-2 text-center {{ $userStats->current_streak >= 3 ? 'opacity-100' : 'opacity-40' }}">
                                        <div
                                            class="w-12 h-12 bg-gray-600 rounded-full mx-auto mb-3 flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-300"
                                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                            </svg>
                                        </div>
                                        <h3 class="font-medium">On Fire</h3>
                                        <p class="text-xs text-gray-400 mt-1">Get a 3-day streak</p>
                                    </div>

                                    <div
                                        class="bg-gray-700 rounded-lg p-2 text-center {{ $userStats->max_streak >= 10 ? 'opacity-100' : 'opacity-40' }}">
                                        <div
                                            class="w-12 h-12 bg-gray-600 rounded-full mx-auto mb-3 flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-300"
                                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                        </div>
                                        <h3 class="font-medium">Buckeye Faithful</h3>
                                        <p class="text-xs text-gray-400 mt-1">Reach a 10-day streak</p>
                                    </div>

                                    <div
                                        class="bg-gray-700 rounded-lg p-2 text-center {{ $userStats->games_won >= 100 ? 'opacity-100' : 'opacity-40' }}">
                                        <div
                                            class="w-12 h-12 bg-gray-600 rounded-full mx-auto mb-3 flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-300"
                                                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                            </svg>
                                        </div>
                                        <h3 class="font-medium">Ohio Expert</h3>
                                        <p class="text-xs text-gray-400 mt-1">Win 100 games</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                </div>

                <!-- Past Puzzles History -->
                <article>
                    <div class="bg-green-600 text-white p-2 px-4 rounded-t-md">
                        <h2 class="text-lg font-semibold">Past Puzzles</h2>
                    </div>
                    <div class="p-2 bg-gray-700 rounded-b-md">
                        <div class="bg-gray-800 p-2 rounded-md">
                            @php
                                // Filter out future puzzles and unplayed puzzles
                                $pastPuzzles = $recentPuzzles->filter(function($puzzle) use ($puzzleProgress) {
                                    // For today's puzzle, only show if it's been completed
                                    if ($puzzle->publish_date->isToday()) {
                                        return isset($puzzleProgress[$puzzle->id]) &&
                                               $puzzleProgress[$puzzle->id] &&
                                               $puzzleProgress[$puzzle->id]->completed_at;
                                    }

                                    // For past puzzles, show any that have been played
                                    return $puzzle->publish_date->isPast() &&
                                           isset($puzzleProgress[$puzzle->id]) &&
                                           $puzzleProgress[$puzzle->id];
                                });
                            @endphp

                            @if(count($pastPuzzles) > 0)
                                <div class="space-y-4">
                                    @foreach($pastPuzzles as $puzzle)
                                        @php
                                            $progress = $puzzleProgress[$puzzle->id] ?? null;
                                        @endphp

                                        @if($progress)
                                            <div class="bg-gray-700 rounded-md p-2">
                                                <div class="flex flex-col md:flex-row">
                                                    <!-- Puzzle Image -->
                                                    <div class="w-full md:w-1/4 mb-4 md:mb-0 md:mr-4">
                                                        @if($puzzle->image_path)
                                                            <img
                                                                src="{{ Storage::url($puzzle->image_path) }}"
                                                                alt="{{ $puzzle->answer }}"
                                                                class="w-full h-auto rounded-md object-cover"
                                                                style="max-height: 150px;"
                                                            >
                                                        @else
                                                            <div
                                                                class="w-full h-32 bg-gray-600 rounded-md flex items-center justify-center">
                                                                <span class="text-gray-400">No image</span>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <!-- Puzzle Details -->
                                                    <div class="flex-1">
                                                        <div class="flex justify-between items-start mb-2">
                                                            <div>
                                                                <h3 class="text-xl font-bold">{{ $puzzle->answer }}</h3>
                                                                <p class="text-gray-400 text-sm">{{ $puzzle->publish_date->format('F j, Y') }}</p>
                                                            </div>
                                                            <div class="hidden md:block">
                                                                @if($progress->solved)
                                                                    <span
                                                                        class="px-2 py-1 bg-green-700 text-green-100 text-xs rounded-full">
                                                                        Solved ({{ $progress->guesses_taken }} {{ Str::plural('guess', $progress->guesses_taken) }})
                                                                    </span>
                                                                @else
                                                                    <span
                                                                        class="px-2 py-1 bg-red-700 text-red-100 text-xs rounded-full">Failed</span>
                                                                @endif
                                                            </div>
                                                        </div>


                                                        <div class="md:hidden">
                                                            @if($progress->solved)
                                                                <span
                                                                    class="px-2 py-1 bg-green-700 text-green-100 text-xs rounded-full">
                                                                        Solved ({{ $progress->guesses_taken }} {{ Str::plural('guess', $progress->guesses_taken) }})
                                                                    </span>
                                                            @else
                                                                <span
                                                                    class="px-2 py-1 bg-red-700 text-red-100 text-xs rounded-full">Failed</span>
                                                            @endif
                                                        </div>

                                                        @if($progress->previous_guesses && count($progress->previous_guesses) > 0)
                                                            <div class="mt-3">
                                                                <h4 class="text-sm font-semibold text-gray-300 mb-1">
                                                                    Your Guesses:</h4>
                                                                <div class="flex flex-wrap gap-2">
                                                                    @foreach($progress->previous_guesses as $index => $guess)
                                                                        <span
                                                                            class="px-2 py-1 text-xs rounded-md {{ $index == array_key_last($progress->previous_guesses) && $progress->solved ? 'bg-green-700 text-white' : 'bg-gray-600 text-gray-200' }}">
                                                                            {{ $guess }}
                                                                        </span>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endif

                                                        @if($puzzle->hint)
                                                            <div class="mt-3 text-sm">
                                                                <span
                                                                    class="text-gray-400">Hint: </span>{{ $puzzle->hint }}
                                                            </div>
                                                        @endif

                                                        @if($puzzle->image_attribution || $puzzle->link)
                                                            <div class="mt-3 border-t border-gray-600 pt-2">
                                                                @if($puzzle->image_attribution)
                                                                    <div class="text-sm text-gray-400 mb-1">
                                                                        <span
                                                                            class="font-semibold text-gray-300">Image:</span> {!! $puzzle->image_attribution !!}
                                                                    </div>
                                                                @endif
                                                                @if($puzzle->link)
                                                                    <div class="text-sm">
                                                                        <a href="{{ $puzzle->link }}" target="_blank"
                                                                           class="text-blue-400 hover:text-blue-300 underline">
                                                                            Learn more about {{ $puzzle->answer }}
                                                                        </a>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <div class="py-8 text-center text-gray-400">
                                    <p>You haven't completed any past puzzles yet.</p>
                                    <p class="text-sm mt-2">Play today's puzzle to see your history here!</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </div>
</x-app-layout>
