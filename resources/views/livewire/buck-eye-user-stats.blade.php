<!-- resources/views/livewire/buck-eye-user-stats.blade.php -->
<div>
    @if($userStats)
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mb-6 text-center">
            <x-well space="0">
                <div class="text-xs text-amber-400 uppercase font-semibold">Games Played</div>
                <div class="text-3xl font-bold text-gray-100">{{ $userStats->games_played }}</div>
            </x-well>
            <x-well space="0">
                <div class="text-xs text-amber-400 uppercase font-semibold">Winning Percentage</div>
                <div
                    class="text-3xl font-bold text-gray-100">{{ $userStats->games_played ? round(($userStats->games_won / $userStats->games_played) * 100) : 0 }}
                    %
                </div>
            </x-well>
            <x-well space="0">
                <div class="text-xs text-amber-400 uppercase font-semibold">Current Streak</div>
                <div class="text-3xl font-bold text-gray-100">{{ $userStats->current_streak }}</div>
            </x-well>
            <x-well space="0">
                <div class="text-xs text-amber-400 uppercase font-semibold">Longest Streak</div>
                <div class="text-3xl font-bold text-gray-100">{{ $userStats->max_streak }}</div>
            </x-well>
        </div>

        @if($userStats->guess_distribution && count($userStats->guess_distribution) > 0)
            <div>
                <h4 class="text-xs uppercase font-semibold mb-2">Guess Distribution</h4>
                <div class="space-y-2">
                    @foreach(range(1, 5) as $guessNumber)
                        @php
                            $count = $userStats->guess_distribution[$guessNumber] ?? 0;
                            $maxCount = max($userStats->guess_distribution ?: [0]);
                            $percentage = $maxCount > 0 ? ($count / $maxCount) * 100 : 0;
                        @endphp
                        <div class="flex items-center">
                            <div class="w-4 text-gray-300 font-bold">{{ $guessNumber }}</div>
                            <div class="flex-1 ml-2">
                                <div
                                    class="text-right px-2 py-1 text-sm font-extrabold rounded-sm bg-green-400 text-green-950"
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
