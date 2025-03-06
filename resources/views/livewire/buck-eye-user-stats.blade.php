<!-- resources/views/livewire/buck-eye-user-stats.blade.php -->
<div>
    @if($userStats)
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mb-6">
            <div class="text-center p-3 bg-gray-700 rounded-lg">
                <div class="text-3xl font-bold">{{ $userStats->games_played }}</div>
                <div class="text-xs text-gray-400">Played</div>
            </div>
            <div class="text-center p-3 bg-gray-700 rounded-lg">
                <div
                    class="text-3xl font-bold">{{ $userStats->games_played ? round(($userStats->games_won / $userStats->games_played) * 100) : 0 }}
                    %
                </div>
                <div class="text-xs text-gray-400">Win Rate</div>
            </div>
            <div class="text-center p-3 bg-gray-700 rounded-lg">
                <div class="text-3xl font-bold">{{ $userStats->current_streak }}</div>
                <div class="text-xs text-gray-400">Current Streak</div>
            </div>
            <div class="text-center p-3 bg-gray-700 rounded-lg">
                <div class="text-3xl font-bold">{{ $userStats->max_streak }}</div>
                <div class="text-xs text-gray-400">Max Streak</div>
            </div>
        </div>

        @if($userStats->guess_distribution && count($userStats->guess_distribution) > 0)
            <div>
                <h3 class="text-lg font-semibold mb-2">Guess Distribution</h3>
                <div class="space-y-2">
                    @foreach(range(1, 5) as $guessNumber)
                        @php
                            $count = $userStats->guess_distribution[$guessNumber] ?? 0;
                            $maxCount = max($userStats->guess_distribution ?: [0]);
                            $percentage = $maxCount > 0 ? ($count / $maxCount) * 100 : 0;
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
            </div>
        @else
            <p class="text-gray-400 text-sm italic">Play more games to see your guess distribution.</p>
        @endif
    @else
        <p class="text-gray-400">Play your first game to see statistics!</p>
    @endif
</div>
