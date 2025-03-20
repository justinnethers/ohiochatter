@if($showPuzzleStats && $puzzleStats)
    <div class="my-2 md:my-4">
        <x-well>
            <div class="flex justify-between">
                <h3 class="text-lg font-bold">Today's Stats</h3>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                <x-well color="gray" shade="800" space="0">
                    <div class="text-xs text-amber-400 uppercase font-semibold">Players</div>
                    <div class="text-3xl font-bold text-gray-100">{{ $puzzleStats['totalPlayers'] }}</div>
                </x-well>

                <x-well color="gray" shade="800" space="0">
                    <div class="text-xs text-amber-400 uppercase font-semibold"># Solved</div>
                    <div class="text-3xl font-bold text-gray-100">{{ $puzzleStats['solvedCount'] }}</div>
                </x-well>

                <x-well color="gray" shade="800" space="0">
                    <div class="text-xs text-amber-400 uppercase font-semibold">Solved %</div>
                    <div class="text-3xl font-bold text-gray-100">{{ $puzzleStats['completionRate'] }}</div>
                </x-well>

                <x-well color="gray" shade="800" space="0">
                    <div class="text-xs text-amber-400 uppercase font-semibold">Avg Guesses</div>
                    <div class="text-3xl font-bold text-gray-100">{{ $puzzleStats['averageGuesses'] }}</div>
                </x-well>
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
                                $isCurrentGuessCount = $gameState['gameWon'] && count($gameState['previousGuesses']) == $guessNumber;
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
