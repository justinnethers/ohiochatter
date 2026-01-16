<div>
    @if($userStats)
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6 text-center">
            <div class="p-3 bg-gray-800 rounded-lg border border-gray-600">
                <div class="text-2xl font-bold text-white">{{ $userStats->games_played }}</div>
                <div class="text-xs text-gray-400 font-medium uppercase tracking-wide">Played</div>
            </div>
            <div class="p-3 bg-gray-800 rounded-lg border border-gray-600">
                <div class="text-2xl font-bold text-white">{{ $userStats->games_played ? round(($userStats->games_won / $userStats->games_played) * 100) : 0 }}%</div>
                <div class="text-xs text-gray-400 font-medium uppercase tracking-wide">Win Rate</div>
            </div>
            <div class="p-3 bg-gray-800 rounded-lg border border-gray-600">
                <div class="text-2xl font-bold text-white">{{ $userStats->current_streak }}</div>
                <div class="text-xs text-gray-400 font-medium uppercase tracking-wide">Streak</div>
            </div>
            <div class="p-3 bg-gray-800 rounded-lg border border-gray-600">
                <div class="text-2xl font-bold text-red-400">{{ $userStats->max_streak }}</div>
                <div class="text-xs text-gray-400 font-medium uppercase tracking-wide">Best</div>
            </div>
        </div>

        @if($userStats->guess_distribution && count($userStats->guess_distribution) > 0)
            <div class="pt-4 border-t border-gray-700">
                <h4 class="text-sm font-bold text-gray-300 mb-3 uppercase tracking-wide">Guess Distribution</h4>
                <div class="space-y-2">
                    @php
                        $maxCount = max($userStats->guess_distribution ?: [0]);
                        $barColors = [
                            1 => 'bg-green-500',
                            2 => 'bg-green-600',
                            3 => 'bg-yellow-500',
                            4 => 'bg-orange-500',
                            5 => 'bg-orange-600',
                            6 => 'bg-red-500',
                        ];
                    @endphp
                    @foreach(range(1, 6) as $guessNumber)
                        @php
                            $count = $userStats->guess_distribution[$guessNumber] ?? 0;
                            $percentage = $maxCount > 0 ? ($count / $maxCount) * 100 : 0;
                        @endphp
                        <div class="flex items-center gap-2">
                            <div class="w-4 text-gray-400 text-sm font-bold">{{ $guessNumber }}</div>
                            <div class="flex-1">
                                <div
                                    class="{{ $barColors[$guessNumber] ?? 'bg-gray-500' }} text-white text-xs px-2 py-1 rounded text-right font-bold"
                                    style="width: max(28px, {{ $percentage }}%)"
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
