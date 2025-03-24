<!-- resources/views/livewire/buck-eye-game-component.blade.php -->
<div>
    @if (!$puzzle)
        <div class="bg-yellow-100 p-4 rounded-lg mb-4">
            <p class="text-yellow-800">{{ $errorMessage ?? 'No puzzle available today. Check back tomorrow!' }}</p>
        </div>
    @else
        <div>
            <div class="lg:grid grid-cols-4 gap-4">
                <div class="lg:hidden text-center flex grow lg:flex-col gap-2 md:gap-4 mb-2 md:mb-4">
                    <div class="flex-1 lg:flex-none">
                        <x-well space="0">
                            <div
                                class="text-xs text-amber-400 uppercase font-semibold">{{ Str::plural('Word', $puzzle->word_count) }}</div>
                            <div class="text-3xl font-bold text-gray-100">{{ $puzzle->word_count }}</div>
                        </x-well>
                    </div>
                    <div class="flex-1 lg:flex-none">
                        <x-well space="0">
                            <div class="text-xs text-amber-400 uppercase font-semibold">Guesses Left</div>
                            <div class="text-3xl font-bold text-gray-100">{{ $gameState['remainingGuesses'] }}</div>
                        </x-well>
                    </div>
                </div>

                <div class="error-message"></div>
                @include('livewire.partials.buck-eye-pixelated-image', [
                    'imageUrl' => $imageUrl,
                    'pixelationLevel' => $gameState['pixelationLevel'],
                    'puzzle' => $puzzle,
                    'gameComplete' => $gameState['gameComplete'],
                    'errorMessage' => $errorMessage
                ])

                <div>
                    <div class="hidden text-center lg:flex grow lg:flex-col gap-4 mb-4">
                        <div class="flex-1 lg:flex-none">
                            <x-well space="0">
                                <div
                                    class="text-xs text-amber-400 uppercase font-semibold">{{ Str::plural('Word', $puzzle->word_count) }}</div>
                                <div class="text-3xl font-bold text-gray-100">{{ $puzzle->word_count }}</div>
                            </x-well>
                        </div>
                        <div class="flex-1 lg:flex-none">
                            <x-well space="0">
                                <div class="text-xs text-amber-400 uppercase font-semibold">Guesses Left</div>
                                <div class="text-3xl font-bold text-gray-100">{{ $gameState['remainingGuesses'] }}</div>
                            </x-well>
                        </div>
                        @if (count($gameState['previousGuesses']) > 0)
                            <div class="flex-1 lg:flex-none">
                                <x-well space="2">
                                    <h3 class="text-xs text-amber-400 uppercase font-semibold mb-2">Your Guesses</h3>
                                    <div class="">
                                        @foreach ($gameState['previousGuesses'] as $guess)
                                            <div
                                                class="font-semibold {{ $loop->last && $gameState['gameWon'] ? 'text-green-400' : 'text-red-400' }}">{{ $guess }}</div>
                                        @endforeach
                                    </div>
                                </x-well>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if($gameState['gameComplete'])
            <x-well>
                <div class="md:flex justify-between space-y-4">
                    <div class="space-y-4 flex-1">
                        <div
                            class="font-bold text-xl {{ $gameState['gameWon'] ? 'text-green-400' : 'text-red-400' }}">{{ $gameState['gameWon'] ? 'You got it!' : 'Better luck tomorrow!' }}</div>
                        <div class="text-lg">The answer was <span class="font-bold">{{ $puzzle->answer }}</span></div>

                        @if($puzzle->link)
                            <div class="text-sm">
                                <a href="{{ $puzzle->link }}" target="_blank"
                                   class="text-blue-300 hover:text-blue-400 underline">
                                    Learn more about {{ $puzzle->answer }}
                                </a>
                            </div>
                        @endif

                        <p class="text-sm text-amber-400">Come back tomorrow for a new puzzle!</p>
                    </div>
                    <div class="flex md:block">
                        <x-primary-button class="share-button flex-1 justify-center">Share Today's Puzzle
                        </x-primary-button>
                    </div>
                </div>
            </x-well>
        @endif

        <!-- Game Complete State -->
        @if ($gameState['gameComplete'])

            @include('livewire.partials.buck-eye-puzzle-stats', [
                'showPuzzleStats' => $showPuzzleStats,
                'puzzleStats' => $puzzleStats,
                'puzzle' => $puzzle
            ])

        @else
            <!-- Guess Input Form -->
            <form wire:submit.prevent="submitGuess" class="mb-2 md:mb-4">
                <div class="flex flex-col md:flex-row gap-2">
                    <input
                        wire:key="current-guess-input"
                        type="text"
                        wire:model="currentGuess"
                        class="text-black flex-1 px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                        placeholder="Enter your guess..."
                        {{ $gameState['gameComplete'] ? 'disabled' : '' }}
                    >
                    <x-primary-button
                        class="px-4 py-2 bg-blue-600 text-white justify-center rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"

                    >
                        {{ __('Submit Guess') }}
                    </x-primary-button>
                </div>
                @error('currentGuess')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </form>
        @endif

        <div class="grid grid-cols-2 gap-2 md:gap-4">
            @if (count($gameState['previousGuesses']) > 0)
                <div class="flex-1 lg:hidden">
                    <x-well space="2">
                        <h3 class="text-xs text-amber-400 uppercase font-semibold mb-2">Your guesses</h3>
                        <div class="text-left">
                            @foreach ($gameState['previousGuesses'] as $guess)
                                <div
                                    class="font-semibold {{ $loop->last && $gameState['gameWon'] ? 'text-green-400' : 'text-red-400' }}">{{ $guess }}</div>
                            @endforeach
                        </div>
                    </x-well>
                </div>
            @endif

            @include('livewire.partials.buck-eye-hints', [
                'remainingGuesses' => $gameState['remainingGuesses'],
                'gameComplete' => $gameState['gameComplete'],
                'puzzle' => $puzzle
            ])
        </div>

    @endif
</div>

<script>
    const shareButton = document.querySelector('.share-button');

    shareButton.addEventListener('click', event => {
        if (navigator.share) {
            navigator.share({
                title: 'BuckEYE Daily Puzzle Game | Ohio Chatter',
                url: 'https://ohiochatter.com/buckEYE'
            }).then(() => {
                console.log('Thanks for sharing!');
            })
                .catch(console.error);
        } else {
            // fallback
        }
    });

    document.addEventListener('livewire:init', () => {
        Livewire.on('clearCurrentGuess', () => {
            const input = document.querySelector('input[wire\\:model="currentGuess"]');
            if (input) {
                input.value = '';
            }
            const statusMessage = document.querySelector('.error-message');

            if (statusMessage) {
                setTimeout(() => {
                    // Scroll with offset for header
                    window.scrollTo({
                        top: statusMessage.getBoundingClientRect().top + window.pageYOffset - 100,
                        behavior: 'smooth'
                    });
                }, 300);
            }
        });
    });
</script>
