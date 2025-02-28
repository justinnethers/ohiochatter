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
            >Back to Game</x-nav-link>
        </div>
    </x-slot>

    <div>
        <div class="md:rounded-lg md:bg-gray-800 p-2 md:p-8 md:pt-4 md:mt-4">
            <div class="max-w-6xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Overall Stats Card -->
                    <div class="bg-gray-700 p-3 md:px-4 md:pt-4 md:pb-5 text-gray-100 font-body rounded md:rounded-md shadow-lg">
                        <div class="bg-red-600 text-white p-4 -mt-4 -mx-4 md:-mx-4 md:rounded-t-md mb-4">
                            <h2 class="text-xl font-semibold">Overall Performance</h2>
                        </div>
                        <div class="p-4 bg-gray-800 rounded-md">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="text-center p-3 bg-gray-700 rounded-lg">
                                    <div class="text-3xl font-bold">{{ $userStats->games_played }}</div>
                                    <div class="text-sm text-gray-400">Games Played</div>
                                </div>
                                <div class="text-center p-3 bg-gray-700 rounded-lg">
                                    <div class="text-3xl font-bold">{{ $userStats->games_won }}</div>
                                    <div class="text-sm text-gray-400">Games Won</div>
                                </div>
                                <div class="text-center p-3 bg-gray-700 rounded-lg">
                                    <div class="text-3xl font-bold">{{ $userStats->games_played ? round(($userStats->games_won / $userStats->games_played) * 100) : 0 }}%</div>
                                    <div class="text-sm text-gray-400">Win Rate</div>
                                </div>
                                <div class="text-center p-3 bg-green-900 rounded-lg">
                                    <div class="text-3xl font-bold">{{ $userStats->max_streak }}</div>
                                    <div class="text-sm text-gray-300">Best Streak</div>
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

                    <!-- Guess Distribution Card -->
                    <div class="bg-gray-700 p-3 md:px-4 md:pt-4 md:pb-5 text-gray-100 font-body rounded md:rounded-md shadow-lg">
                        <div class="bg-blue-600 text-white p-4 -mt-4 -mx-4 md:-mx-4 md:rounded-t-md mb-4">
                            <h2 class="text-xl font-semibold">Guess Distribution</h2>
                        </div>
                        <div class="p-4 bg-gray-800 rounded-md">
                            @if($userStats->guess_distribution && count($userStats->guess_distribution) > 0)
                                <div class="space-y-3">
                                    @foreach(range(1, 5) as $guessNumber)
                                        @php
                                            $count = $userStats->guess_distribution[$guessNumber] ?? 0;
                                            $totalWins = array_sum($userStats->guess_distribution);
                                            $percentage = $totalWins > 0 ? ($count / $totalWins) * 100 : 0;
                                        @endphp
                                        <div class="flex items-center">
                                            <div class="w-6 text-gray-300 font-medium">{{ $guessNumber }}</div>
                                            <div class="flex-1 ml-2">
                                                <div
                                                    class="bg-blue-600 text-white px-3 py-2 rounded-sm"
                                                    style="width: {{ max(10, $percentage) }}%"
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

                    <!-- Achievements Card -->
                    <div class="bg-gray-700 p-3 md:px-4 md:pt-4 md:pb-5 text-gray-100 font-body rounded md:rounded-md shadow-lg">
                        <div class="bg-purple-600 text-white p-4 -mt-4 -mx-4 md:-mx-4 md:rounded-t-md mb-4">
                            <h2 class="text-xl font-semibold">Achievements</h2>
                        </div>
                        <div class="p-4 bg-gray-800 rounded-md">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-gray-700 rounded-lg p-4 text-center {{ $userStats->games_played >= 1 ? 'opacity-100' : 'opacity-40' }}">
                                    <div class="w-12 h-12 bg-gray-600 rounded-full mx-auto mb-3 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <h3 class="font-medium">First Game</h3>
                                    <p class="text-xs text-gray-400 mt-1">Play your first game</p>
                                </div>

                                <div class="bg-gray-700 rounded-lg p-4 text-center {{ $userStats->games_won >= 1 ? 'opacity-100' : 'opacity-40' }}">
                                    <div class="w-12 h-12 bg-gray-600 rounded-full mx-auto mb-3 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <h3 class="font-medium">First Win</h3>
                                    <p class="text-xs text-gray-400 mt-1">Win your first game</p>
                                </div>

                                <div class="bg-gray-700 rounded-lg p-4 text-center {{ $userStats->current_streak >= 3 ? 'opacity-100' : 'opacity-40' }}">
                                    <div class="w-12 h-12 bg-gray-600 rounded-full mx-auto mb-3 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                    </div>
                                    <h3 class="font-medium">On Fire</h3>
                                    <p class="text-xs text-gray-400 mt-1">Get a 3-day streak</p>
                                </div>

                                <div class="bg-gray-700 rounded-lg p-4 text-center {{ $userStats->max_streak >= 7 ? 'opacity-100' : 'opacity-40' }}">
                                    <div class="w-12 h-12 bg-gray-600 rounded-full mx-auto mb-3 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <h3 class="font-medium">Buckeye Faithful</h3>
                                    <p class="text-xs text-gray-400 mt-1">Reach a 7-day streak</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Past Puzzles History -->
                <div class="mt-6 bg-gray-700 p-3 md:px-4 md:pt-4 md:pb-5 text-gray-100 font-body rounded md:rounded-md shadow-lg">
                    <div class="bg-green-600 text-white p-4 -mt-4 -mx-4 md:-mx-4 md:rounded-t-md mb-4">
                        <h2 class="text-xl font-semibold">Past Puzzles</h2>
                    </div>
                    <div class="p-4 bg-gray-800 rounded-md">
                        @php
                            // Filter out future puzzles and unplayed puzzles
                            $pastPuzzles = $recentPuzzles->filter(function($puzzle) use ($puzzleProgress) {
                                // Keep only past puzzles that have been played
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
                                        <div class="bg-gray-700 rounded-md p-4">
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
                                                        <div class="w-full h-32 bg-gray-600 rounded-md flex items-center justify-center">
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
                                                        <div>
                                                            @if($progress->solved)
                                                                <span class="px-2 py-1 bg-green-700 text-green-100 text-xs rounded-full">
                                                                    Solved ({{ $progress->guesses_taken }} guesses)
                                                                </span>
                                                            @else
                                                                <span class="px-2 py-1 bg-red-700 text-red-100 text-xs rounded-full">Failed</span>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    @if($progress->previous_guesses && count($progress->previous_guesses) > 0)
                                                        <div class="mt-3">
                                                            <h4 class="text-sm font-semibold text-gray-300 mb-1">Your Guesses:</h4>
                                                            <div class="flex flex-wrap gap-2">
                                                                @foreach($progress->previous_guesses as $index => $guess)
                                                                    <span class="px-2 py-1 text-xs rounded-md {{ $index == array_key_last($progress->previous_guesses) && $progress->solved ? 'bg-green-700 text-white' : 'bg-gray-600 text-gray-200' }}">
                                                                        {{ $guess }}
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif

                                                    @if($puzzle->hint)
                                                        <div class="mt-3 text-sm">
                                                            <span class="text-gray-400">Hint: </span>{{ $puzzle->hint }}
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
            </div>
        </div>
    </div>
</x-app-layout>
