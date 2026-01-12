<div
    x-data="{
        currentGuess: @entangle('currentGuess'),
        handleKeydown(event) {
            if ($wire.gameState.gameComplete) return;

            const key = event.key.toUpperCase();

            if (key === 'ENTER') {
                event.preventDefault();
                $wire.submitGuess();
            } else if (key === 'BACKSPACE') {
                event.preventDefault();
                $wire.removeLetter();
            } else if (key.length === 1 && key.match(/[A-Z]/)) {
                event.preventDefault();
                $wire.addLetter(key);
            }
        }
    }"
    x-init="$el.focus()"
    @keydown.window="handleKeydown($event)"
    tabindex="0"
    class="outline-none"
>
    @if (!$word)
        <div class="bg-yellow-100 p-4 rounded-lg mb-4">
            <p class="text-yellow-800">{{ $errorMessage ?? 'No puzzle available today. Check back tomorrow!' }}</p>
        </div>
    @else
        <div class="max-w-lg mx-auto">
            {{-- Error Message --}}
            @if($errorMessage)
                <div class="bg-red-900/50 border border-red-500 text-red-200 px-4 py-2 rounded mb-4 text-center error-message">
                    {{ $errorMessage }}
                </div>
            @endif

            {{-- Game Grid --}}
            <div class="flex flex-col items-center gap-1 mb-4">
                @for($row = 0; $row < 6; $row++)
                    <div class="flex gap-1">
                        @for($col = 0; $col < $wordLength; $col++)
                            @php
                                $letter = '';
                                $status = 'empty';

                                if (isset($gameState['guesses'][$row])) {
                                    // This is a submitted guess
                                    $guess = $gameState['guesses'][$row];
                                    $letter = strtoupper($guess[$col] ?? '');
                                    $status = $gameState['feedback'][$row][$col] ?? 'empty';
                                } elseif ($row === count($gameState['guesses']) && !$gameState['gameComplete']) {
                                    // This is the current input row
                                    $letter = strtoupper($currentGuess[$col] ?? '');
                                    $status = $letter ? 'pending' : 'empty';
                                }

                                $bgColor = match($status) {
                                    'correct' => 'bg-red-600',
                                    'present' => 'bg-gray-400',
                                    'absent' => 'bg-gray-700',
                                    'pending' => 'bg-gray-600 border-gray-400',
                                    default => 'bg-gray-800 border-gray-600',
                                };
                            @endphp
                            <div
                                class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center text-2xl font-bold text-white border-2 rounded {{ $bgColor }} transition-all duration-200"
                                @if($status !== 'empty' && $status !== 'pending')
                                    style="animation: flip 0.3s ease-in-out {{ $col * 0.1 }}s"
                                @endif
                            >
                                {{ $letter }}
                            </div>
                        @endfor
                    </div>
                @endfor
            </div>

            {{-- Game Complete State --}}
            @if($gameState['gameComplete'])
                <div class="space-y-4">
                    {{-- Answer Card --}}
                    <div class="bg-gray-900 rounded-xl border-2 border-gray-700 p-6 text-center shadow-lg">
                        <div class="font-bold text-2xl {{ $gameState['gameWon'] ? 'text-green-400' : 'text-red-400' }} mb-3">
                            {{ $gameState['gameWon'] ? 'Excellent!' : 'Better luck tomorrow!' }}
                        </div>
                        <div class="text-lg text-gray-200 mb-1">
                            The answer was <span class="font-bold text-red-400 text-xl">{{ $gameState['answer'] }}</span>
                        </div>
                        <p class="text-sm text-gray-400 mb-4">Come back tomorrow for a new puzzle!</p>
                        <x-primary-button type="button" onclick="shareResults()">
                            Share Results
                        </x-primary-button>
                    </div>

                    {{-- Word Stats Card --}}
                    @if($showWordStats && $wordStats)
                        <div class="bg-gray-900 rounded-xl border-2 border-gray-700 p-6 shadow-lg">
                            <h3 class="text-lg font-bold text-white mb-4">Today's Stats</h3>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-center">
                                <div class="p-3 bg-gray-800 rounded-lg border border-gray-600">
                                    <div class="text-2xl font-bold text-white">{{ $wordStats['totalPlayers'] }}</div>
                                    <div class="text-xs text-gray-400 font-medium uppercase tracking-wide">Players</div>
                                </div>
                                <div class="p-3 bg-gray-800 rounded-lg border border-gray-600">
                                    <div class="text-2xl font-bold text-white">{{ $wordStats['completionRate'] }}%</div>
                                    <div class="text-xs text-gray-400 font-medium uppercase tracking-wide">Solved</div>
                                </div>
                                <div class="p-3 bg-gray-800 rounded-lg border border-gray-600">
                                    <div class="text-2xl font-bold text-white">{{ $wordStats['averageGuesses'] }}</div>
                                    <div class="text-xs text-gray-400 font-medium uppercase tracking-wide">Average</div>
                                </div>
                                <div class="p-3 bg-gray-800 rounded-lg border border-gray-600">
                                    <div class="text-2xl font-bold text-white">{{ $wordStats['solvedCount'] }}</div>
                                    <div class="text-xs text-gray-400 font-medium uppercase tracking-wide">Winners</div>
                                </div>
                            </div>

                            @if($wordStats['guessDistribution'])
                                <div class="mt-5 pt-4 border-t border-gray-700">
                                    <h4 class="text-sm font-bold text-gray-300 mb-3 uppercase tracking-wide">Guess Distribution</h4>
                                    <div class="space-y-2">
                                        @php $maxCount = max($wordStats['guessDistribution'] ?: [0]); @endphp
                                        @foreach($wordStats['guessDistribution'] as $guessNum => $count)
                                            @php $percentage = $maxCount > 0 ? ($count / $maxCount) * 100 : 0; @endphp
                                            <div class="flex items-center gap-2">
                                                <div class="w-4 text-gray-400 text-sm font-bold">{{ $guessNum }}</div>
                                                <div class="flex-1">
                                                    <div
                                                        class="bg-red-600 text-white text-xs px-2 py-1 rounded text-right font-bold"
                                                        style="width: max(28px, {{ $percentage }}%)"
                                                    >
                                                        {{ $count }}
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @endif

            {{-- Virtual Keyboard --}}
            @if(!$gameState['gameComplete'])
                <div class="flex flex-col items-center gap-1">
                    @php
                        $keyboardRows = [
                            ['Q', 'W', 'E', 'R', 'T', 'Y', 'U', 'I', 'O', 'P'],
                            ['A', 'S', 'D', 'F', 'G', 'H', 'J', 'K', 'L'],
                            ['ENTER', 'Z', 'X', 'C', 'V', 'B', 'N', 'M', 'BACK'],
                        ];
                    @endphp

                    @foreach($keyboardRows as $rowIndex => $row)
                        <div class="flex gap-1 justify-center">
                            @foreach($row as $key)
                                @php
                                    $isSpecial = in_array($key, ['ENTER', 'BACK']);
                                    $keyState = $keyboardState[$key] ?? 'unused';

                                    $bgColor = match($keyState) {
                                        'correct' => 'bg-red-600 hover:bg-red-500',
                                        'present' => 'bg-gray-400 hover:bg-gray-300',
                                        'absent' => 'bg-gray-700 hover:bg-gray-600',
                                        default => 'bg-gray-500 hover:bg-gray-400',
                                    };

                                    $width = $isSpecial ? 'min-w-[60px] md:min-w-[70px]' : 'min-w-[32px] md:min-w-[40px]';
                                @endphp
                                <button
                                    type="button"
                                    wire:click="{{ $key === 'ENTER' ? 'submitGuess' : ($key === 'BACK' ? 'removeLetter' : 'addLetter(\'' . $key . '\')') }}"
                                    class="{{ $width }} h-12 md:h-14 px-1 md:px-2 rounded font-bold text-white text-sm md:text-base {{ $bgColor }} transition-all duration-150 flex items-center justify-center active:scale-95 active:brightness-90 shadow-sm hover:shadow-md"
                                >
                                    @if($key === 'BACK')
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z" />
                                        </svg>
                                    @else
                                        {{ $key }}
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    @endforeach
                </div>

                <p class="text-center text-gray-400 text-sm mt-4">
                    Guesses remaining: <span class="font-bold text-white">{{ $gameState['remainingGuesses'] }}</span>
                </p>
            @endif
        </div>
    @endif

    <style>
        @keyframes flip {
            0% {
                transform: rotateX(0);
            }
            50% {
                transform: rotateX(90deg);
            }
            100% {
                transform: rotateX(0);
            }
        }
    </style>

    <script>
        function shareResults() {
            const shareText = @json($this->getShareText());

            if (navigator.share) {
                navigator.share({
                    title: 'OhioWordle',
                    text: shareText,
                    url: 'https://ohiochatter.com/ohiowordle'
                }).catch(console.error);
            } else if (navigator.clipboard) {
                navigator.clipboard.writeText(shareText + '\nhttps://ohiochatter.com/ohiowordle').then(() => {
                    alert('Results copied to clipboard!');
                }).catch(console.error);
            }
        }

        document.addEventListener('livewire:init', () => {
            Livewire.on('clearCurrentGuess', () => {
                const errorMessage = document.querySelector('.error-message');
                if (errorMessage) {
                    setTimeout(() => {
                        window.scrollTo({
                            top: errorMessage.getBoundingClientRect().top + window.pageYOffset - 100,
                            behavior: 'smooth'
                        });
                    }, 300);
                }
            });
        });
    </script>
</div>
