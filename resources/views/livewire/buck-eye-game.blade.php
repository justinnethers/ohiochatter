<!-- resources/views/livewire/buck-eye-game-component.blade.php -->
<div class="max-w-4xl mx-auto space-y-4">
    <div class="text-center text-gray-100">
        <h1 class="text-2xl font-bold 00 mb-2">BuckEYE - Test Your Knowledge of All Things Ohio</h1>
        <p class="text-600">Each day's puzzle is related to Ohio in some way. How well do you know the Buckeye
            State?</p>
    </div>

    @if (!$puzzle)
        <div class="bg-yellow-100 p-4 rounded-lg mb-4">
            <p class="text-yellow-800">{{ $errorMessage ?? 'No puzzle available today. Check back tomorrow!' }}</p>
        </div>
    @else
        <!-- Word Count Indicator -->
        <div class="mb-4 text-center text-gray-100">
            <p class="text-lg font-semibold">
                Today's answer has <span
                    class="text-red-500 text-2xl">{{ $wordCount }}</span> {{ Str::plural('word', $wordCount) }}
            </p>
        </div>

        <div class="status-message">
            <!-- Game Status Messages -->
            @if ($errorMessage)
                <div class="bg-red-100 p-4 rounded-lg mb-4">
                    <p class="text-red-800">{{ $errorMessage }}</p>
                </div>
            @endif

            @if ($successMessage)
                <div class="bg-green-100 p-4 rounded-lg mb-4">
                    <p class="text-green-800">{{ $successMessage }}</p>
                </div>
            @endif
        </div>

        <!-- Updated Pixelated Image Section with Overlay Protection -->
        <div class="flex justify-center">
            <div>
                <!-- Transparent overlay div to prevent image selection -->
                <div class="w-full max-w-md overflow-hidden rounded-lg shadow-lg relative">
                    <div class="absolute inset-0 w-full h-full z-10"
                         style="pointer-events: auto; user-select: none; -webkit-user-select: none;"
                         oncontextmenu="return false;">
                    </div>
                    <img
                        src="{{ $imageUrl }}"
                        alt="Pixelated Ohio Item"
                        class="w-full select-none pointer-events-none"
                        style="filter: blur({{ max(0, $pixelationLevel * 6) }}px); image-rendering: pixelated; -webkit-user-select: none; user-select: none;"
                        wire:key="image-{{ $pixelationLevel }}"
                        draggable="false"
                    >
                </div>
                @if($gameComplete && ($puzzle->image_attribution || $puzzle->link))
                    <div class="mt-2 text-center">
                        @if($puzzle->image_attribution)
                            <div class="text-sm text-gray-200 mb-1">
                                <span class="font-semibold">Image:</span> {!! $puzzle->image_attribution !!}
                            </div>
                        @endif
                        @if($puzzle->link)
                            <div class="text-sm">
                                <a href="{{ $puzzle->link }}" target="_blank"
                                   class="text-blue-600 hover:text-blue-800 underline">
                                    Learn more about {{ $puzzle->answer }}
                                </a>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="text-center">
            <p class="text-gray-100">
                <span class="font-semibold">Remaining guesses:</span> {{ $remainingGuesses }}
            </p>
        </div>

        @if (count($previousGuesses) > 0)
            <div class="text-gray-100">
                <h3 class="text-lg font-semibold mb-2">Your guesses:</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach ($previousGuesses as $guess)
                        <span class="px-2 py-0.5 bg-gray-200 rounded-full text-gray-800">{{ $guess }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Game Complete State -->
        @if ($gameComplete)
            <div class="p-2 md:p-4 space-y-2 rounded-lg {{ $gameWon ? 'bg-green-100' : 'bg-red-100' }}">
                <h3 class="text-xl font-bold {{ $gameWon ? 'text-green-800' : 'text-red-800' }}">
                    {{ $gameWon ? 'Congratulations!' : 'Better luck tomorrow!' }}
                </h3>
                <p class="text-gray-800">
                    The answer was <span class="font-bold">{{ $puzzle->answer }}</span>
                </p>
                <p class="text-sm text-gray-600">Come back tomorrow for a new puzzle!</p>
            </div>
            <!-- Puzzle Statistics for completed games -->
            @if($showPuzzleStats && $puzzleStats)
                <div class="mt-4 p-4 bg-blue-50 text-blue-900 rounded-lg space-y-4">
                    <h3 class="text-lg font-bold">Today's Puzzle Stats</h3>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-white p-3 rounded-lg shadow text-center">
                            <div class="text-xl md:text-2xl font-bold">{{ $puzzleStats['totalPlayers'] }}</div>
                            <div class="text-xs text-gray-600">Players</div>
                        </div>

                        <div class="bg-white p-3 rounded-lg shadow text-center">
                            <div class="text-xl md:text-2xl font-bold">{{ $puzzleStats['completionRate'] }}%</div>
                            <div class="text-xs text-gray-600">Solved</div>
                        </div>

                        <div class="bg-white p-3 rounded-lg shadow text-center">
                            <div class="text-xl md:text-2xl font-bold">{{ $puzzleStats['averageGuesses'] }}</div>
                            <div class="text-xs text-gray-600">Avg Guesses</div>
                        </div>

                        <div class="bg-white p-3 rounded-lg shadow text-center">
                            <div class="text-xl md:text-2xl font-bold">{{ $puzzleStats['solvedCount'] }}</div>
                            <div class="text-xs text-gray-600">Successes</div>
                        </div>
                    </div>

                    @if(count($puzzleStats['guessDistribution']) > 0)
                        <div>
                            <h4 class="font-semibold mb-2">Guess Distribution</h4>
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
                                        <div class="w-4 text-gray-700">{{ $guessNumber }}</div>
                                        <div class="flex-1 ml-2">
                                            <div
                                                class="text-right px-2 py-1 text-sm font-medium rounded-sm {{ $isCurrentGuessCount ? 'bg-green-600 text-white' : 'bg-blue-100 text-blue-900' }}"
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
                </div>
            @endif
        @else
            <!-- Guess Input Form -->
            <form wire:submit.prevent="submitGuess" class="mb-6">
                <div class="flex flex-col md:flex-row gap-2">
                    <input
                        wire:key="current-guess-input"
                        type="text"
                        wire:model="currentGuess"
                        class="text-black flex-1 px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                        placeholder="Enter your guess..."
                        {{ $gameComplete ? 'disabled' : '' }}
                    >
                    <x-primary-button
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"

                    >
                        {{ __('Submit Guess') }}
                    </x-primary-button>
                </div>
                @error('currentGuess')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </form>
        @endif

        <!-- Hint -->
        @if (!$gameComplete && $puzzle->category && $remainingGuesses <= 3 || $gameComplete)
            <div class="mt-6 p-4 bg-blue-100 rounded-lg">
                <h4 class="font-semibold mb-1">Hints</h4>
                <p>{{ Str::title($puzzle->category) }}</p>
                @if ($puzzle->hint && $remainingGuesses <= 2 || $gameComplete)
                    <p>{{ $puzzle->hint }}</p>
                @endif
                @if ($puzzle->hint_2 && $remainingGuesses <= 1 || $gameComplete)
                    <p>{{ $puzzle->hint_2 }}</p>
                @endif
            </div>
        @endif
    @endif
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('clearCurrentGuess', () => {
            const input = document.querySelector('input[wire\\:model="currentGuess"]');
            if (input) {
                input.value = '';
            }
            const statusMessage = document.querySelector('.status-message');

            if (statusMessage) {
                setTimeout(() => {
                    // Scroll with offset for header
                    window.scrollTo({
                        top: statusMessage.getBoundingClientRect().top + window.pageYOffset - 100,
                        behavior: 'smooth'
                    });
                }, 100);
            }
        });
    });
</script>
