<!-- resources/views/livewire/buck-eye-game-component.blade.php -->
<div class="max-w-4xl mx-auto">
    <div class="mb-6 text-center text-gray-100">
        <h1 class="text-2xl font-bold 00 mb-2">BuckEYE</h1>
        <p class="text-600">Guess the Ohio-related answer!</p>
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
                    class="text-red-500">{{ $wordCount }}</span> {{ Str::plural('word', $wordCount) }}
            </p>
        </div>

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

        <!-- Pixelated Image -->
        <div class="mb-6 flex justify-center">
            <div class="w-full max-w-md overflow-hidden rounded-lg shadow-lg">
                <img
                    src="{{ $imageUrl }}"
                    alt="Pixelated Ohio Item"
                    class="w-full"
                    style="filter: blur({{ max(0, $pixelationLevel * 4) }}px); image-rendering: pixelated;"
                    wire:key="image-{{ $pixelationLevel }}"
                >
            </div>
        </div>

        <!-- Game Information -->
        <div class="mb-6 text-center">
            <p class="text-gray-100">
                <span class="font-semibold">Remaining guesses:</span> {{ $remainingGuesses }}
            </p>
        </div>

        <!-- Previous Guesses -->
        @if (count($previousGuesses) > 0)
            <div class="mb-6 text-gray-100">
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
            <div class="mb-6 p-2 space-y-2 rounded-lg {{ $gameWon ? 'bg-green-100' : 'bg-red-100' }}">
                <h3 class="text-xl font-bold {{ $gameWon ? 'text-green-800' : 'text-red-800' }}">
                    {{ $gameWon ? 'Congratulations!' : 'Better luck tomorrow!' }}
                </h3>
                <p class="text-gray-800">
                    The answer was <span class="font-bold">{{ $puzzle->answer }}</span>
                </p>
                <p class="text-sm text-gray-600">Come back tomorrow for a new puzzle!</p>
            </div>
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
                    <button
                        type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                        {{ $gameComplete ? 'disabled' : '' }}
                    >
                        Submit Guess
                    </button>
                </div>
                @error('currentGuess')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </form>
        @endif

        <!-- Hint -->
        @if (!$gameComplete && $puzzle->hint && $remainingGuesses <= 3)
            <div class="mt-6 p-4 bg-blue-100 rounded-lg">
                <h4 class="font-semibold mb-1">Hint:</h4>
                <p>{{ $puzzle->hint }}</p>
            </div>
        @endif
    @endif
</div>


<script>
    window.addEventListener('clearCurrentGuess', () => {
        const input = document.querySelector('input[wire\\:model="currentGuess"]');
        if (input) {
            input.value = '';
        }
    });
</script>
