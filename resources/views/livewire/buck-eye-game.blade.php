<!-- resources/views/livewire/buck-eye-game-component.blade.php -->
<div class="max-w-4xl mx-auto">
    <div class="text-center text-gray-100">
    </div>

    @if (!$puzzle)
        <div class="bg-yellow-100 p-4 rounded-lg mb-4">
            <p class="text-yellow-800">{{ $errorMessage ?? 'No puzzle available today. Check back tomorrow!' }}</p>
        </div>
    @else

        <div class="flex justify-center">
            <div class="lg:grid grid-cols-4 gap-4">

                <div class="lg:hidden text-center flex grow lg:flex-col gap-2 md:gap-4 mb-2 md:mb-4">
                    <div class="flex-1 lg:flex-none">
                        <x-well space="0">
                            <div
                                class="text-xs text-amber-400 uppercase font-semibold">{{ Str::plural('Word', $wordCount) }}</div>
                            <div class="text-3xl font-bold text-gray-100">{{ $wordCount }}</div>
                        </x-well>
                    </div>
                    <div class="flex-1 lg:flex-none">
                        <x-well space="0">
                            <div class="text-xs text-amber-400 uppercase font-semibold">Guesses Left</div>
                            <div class="text-3xl font-bold text-gray-100">{{ $remainingGuesses }}</div>
                        </x-well>
                    </div>
                </div>

                <div class="col-span-4 status-message">
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

                @include('livewire.partials.buck-eye-pixelated-image', [
                    'imageUrl' => $imageUrl,
                    'pixelationLevel' => $pixelationLevel,
                    'puzzle' => $puzzle,
                    'gameComplete' => $gameComplete
                ])

                <div>
                    <div class="hidden text-center lg:flex grow lg:flex-col gap-4 mb-4">
                        <div class="flex-1 lg:flex-none">
                            <x-well space="0">
                                <div
                                    class="text-xs text-amber-400 uppercase font-semibold">{{ Str::plural('Word', $wordCount) }}</div>
                                <div class="text-3xl font-bold text-gray-100">{{ $wordCount }}</div>
                            </x-well>
                        </div>
                        <div class="flex-1 lg:flex-none">
                            <x-well space="0">
                                <div class="text-xs text-amber-400 uppercase font-semibold">Guesses Left</div>
                                <div class="text-3xl font-bold text-gray-100">{{ $remainingGuesses }}</div>
                            </x-well>
                        </div>
                        @if (count($previousGuesses) > 0)
                            <div class="flex-1 lg:flex-none">
                                <x-well space="2">
                                    <h3 class="text-xs text-amber-400 uppercase font-semibold mb-2">Your Guesses</h3>
                                    <div class="">
                                        @foreach ($previousGuesses as $guess)
                                            <div
                                                class="font-semibold {{ $loop->last && $gameWon ? 'text-green-400' : 'text-red-400' }}">{{ $guess }}</div>
                                        @endforeach
                                    </div>
                                </x-well>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if($gameComplete)
            <x-well>
                <h3 class="text-xl {{ $gameWon ? 'text-green-400' : 'text-red-400' }}">
                    <span class="font-bold">{{ $gameWon ? 'Congratulations!' : 'Better luck tomorrow!' }}</span>
                    The answer was <span class="font-bold">{{ $puzzle->answer }}</span>
                </h3>

                <p class="text-sm text-amber-400">Come back tomorrow for a new puzzle!</p>
            </x-well>
        @endif

        <!-- Game Complete State -->
        @if ($gameComplete)

            @include('livewire.partials.buck-eye-puzzle-stats', [
                'showPuzzleStats' => $showPuzzleStats,
                'puzzleStats' => $puzzleStats,
                'puzzle' => $puzzle
            ])

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

        @if (count($previousGuesses) > 0)
            <div class="my-2 md:my-4 flex-1 lg:hidden">
                <x-well space="2">
                    <h3 class="text-xs text-amber-400 uppercase font-semibold mb-2">Your guesses</h3>
                    <div class="text-left">
                        @foreach ($previousGuesses as $guess)
                            <div
                                class="font-semibold {{ $loop->last && $gameWon ? 'text-green-400' : 'text-red-400' }}">{{ $guess }}</div>
                        @endforeach
                    </div>
                </x-well>
            </div>
        @endif

        @include('livewire.partials.buck-eye-hints', [
            'remainingGuesses' => $remainingGuesses,
            'gameComplete' => $gameComplete,
            'puzzle' => $puzzle
        ])

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
                        top: statusMessage.getBoundingClientRect().top + window.pageYOffset - 120,
                        behavior: 'smooth'
                    });
                }, 100);
            }
        });
    });
</script>
