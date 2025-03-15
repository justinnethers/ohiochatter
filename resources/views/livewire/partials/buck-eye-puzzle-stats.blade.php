@if($showPuzzleStats && $puzzleStats)
    <div class="my-2 md:my-4">
        <x-well>
            <h3 class="text-lg font-bold">Today's Stats</h3>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <x-well-grid-item>
                    <div class="text-xl md:text-2xl font-bold">{{ $puzzleStats['totalPlayers'] }}</div>
                    <div class="text-xs text-gray-300">Players</div>
                </x-well-grid-item>

                <x-well-grid-item>
                    <div class="text-xl md:text-2xl font-bold">{{ $puzzleStats['solvedCount'] }}</div>
                    <div class="text-xs text-gray-300">Solved</div>
                </x-well-grid-item>

                <x-well-grid-item>
                    <div class="text-xl md:text-2xl font-bold">{{ $puzzleStats['completionRate'] }}%</div>
                    <div class="text-xs text-gray-300">Solved</div>
                </x-well-grid-item>

                <x-well-grid-item>
                    <div class="text-xl md:text-2xl font-bold">{{ $puzzleStats['averageGuesses'] }}</div>
                    <div class="text-xs text-gray-300">Avg Guesses</div>
                </x-well-grid-item>
            </div>

            @if(count($puzzleStats['guessDistribution']) > 0)
                <div>
                    <h4 class="text-xs uppercase font-semibold mb-2">Guess Distribution</h4>
                    <div class="space-y-2">
                        @php
                            $maxCount = max($puzzleStats['guessDistribution']);
                        @endphp

                        @foreach(range(1, 5) as $guessNumber)
                            @php
                                $count = $puzzleStats['guessDistribution'][$guessNumber] ?? 0;
                                $percentage = $maxCount > 0 ? ($count / $maxCount) * 100 : 0;
                                $isCurrentGuessCount = $gameWon && count($previousGuesses) == $guessNumber;
                            @endphp
                            <div class="flex items-center">
                                <div class="w-4 text-gray-300 font-bold">{{ $guessNumber }}</div>
                                <div class="flex-1 ml-2">
                                    <div
                                        class="text-right px-2 py-1 text-sm font-extrabold rounded-sm {{ $isCurrentGuessCount ? 'bg-green-400 text-green-950' : 'bg-white/10 text-white-200' }}"
                                        style="width: {{ max(5, $percentage) }}%"
                                    >
                                        {{ $count }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </x-well>
    </div>

@endif
