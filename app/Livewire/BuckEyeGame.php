<?php

namespace App\Livewire;

use App\Models\Puzzle;
use App\Models\UserGameStats;
use App\Services\PuzzleService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BuckEyeGame extends Component
{
    /**
     * The current puzzle
     */
    public $puzzle;

    /**
     * The current user's input guess
     */
    public $currentGuess = '';

    /**
     * Previous guesses made
     */
    public $previousGuesses = [];

    /**
     * Remaining guesses count
     */
    public $remainingGuesses = 5;

    /**
     * Current pixelation level (5 = most pixelated, 0 = clear)
     */
    public $pixelationLevel = 5;

    /**
     * Whether the game is complete
     */
    public $gameComplete = false;

    /**
     * Whether the user won the game
     */
    public $gameWon = false;

    /**
     * The pixelated image URL
     */
    public $imageUrl;

    /**
     * Word count for the answer
     */
    public $wordCount;

    /**
     * User game stats
     */
    public $userStats;

    /**
     * Error message
     */
    public $errorMessage;

    /**
     * Success message
     */
    public $successMessage;

    /**
     * Rules for validation
     */
    protected $rules = [
        'currentGuess' => 'required|string|min:2',
    ];

    /**
     * Initialize the component
     */
    public function mount(PuzzleService $puzzleService)
    {
        $this->puzzle = $puzzleService->getTodaysPuzzle();

        if (!$this->puzzle) {
            $this->errorMessage = "No puzzle available for today.";
            return;
        }

        $this->wordCount = $this->puzzle->word_count;

        // If user is authenticated, load their progress
        if (Auth::check()) {
            $progress = $puzzleService->getUserGameProgress(Auth::user());

            if ($progress) {
                $this->previousGuesses = $progress->previous_guesses ?: [];
                $this->remainingGuesses = PuzzleService::MAX_GUESSES - $progress->attempts;
                $this->pixelationLevel = PuzzleService::PIXELATION_LEVELS - $progress->attempts;

                // Make sure we show clear image if the game was won
                if ($progress->solved) {
                    $this->pixelationLevel = 0;
                } elseif ($this->pixelationLevel < 0) {
                    $this->pixelationLevel = 0;
                }

                $this->gameComplete = (bool) $progress->completed_at;
                $this->gameWon = $progress->solved;

                // Load user stats
                $this->userStats = UserGameStats::getOrCreateForUser(Auth::id());
            }
        }

        // Get the image
        $this->imageUrl = $puzzleService->getPixelatedImage($this->puzzle, $this->pixelationLevel);
    }

    public function submitGuess(PuzzleService $puzzleService)
    {
        if ($this->gameComplete) {
            $this->errorMessage = "This game is already complete.";
            return;
        }

        // Validate using the current value before clearing it
        $this->validate();

        // Store the guess and then clear the input
        $currentGuess = $this->currentGuess;

        // Process the guess using our stored value
        $result = $puzzleService->processGuess(Auth::user(), $currentGuess);

        // Update component state based on result
        if ($result['status'] === 'error') {
            $this->errorMessage = $result['message'];
            return;
        }

        // Clear any existing messages
        $this->errorMessage = null;
        $this->successMessage = null;

        // Update game state
        $this->previousGuesses[] = $currentGuess;
        $this->remainingGuesses = $result['remaining_guesses'];
        $this->pixelationLevel = $result['pixelation_level'];
        $this->gameComplete = $result['game_complete'];
        $this->gameWon = $result['game_won'];

        // Always set pixelation to 0 (clear) if the game is won
        if ($this->gameWon) {
            $this->pixelationLevel = 0;
        }

        // Update the image URL
        $this->imageUrl = $puzzleService->getPixelatedImage($this->puzzle, $this->pixelationLevel);

        // Set appropriate message
        if ($result['status'] === 'correct') {
            $this->successMessage = $result['message'];
        } else {
            $this->errorMessage = $result['message'];
        }

        // Refresh user stats if game is complete
        if ($this->gameComplete && Auth::check()) {
            $this->userStats = UserGameStats::getOrCreateForUser(Auth::id());

            // Emit an event to update the user stats component
            $this->dispatch('gameCompleted', [
                'won' => $this->gameWon,
                'guesses' => count($this->previousGuesses)
            ]);
        }

        $this->currentGuess = '';
        $this->dispatch('clearCurrentGuess');
    }

    public function render()
    {
        return view('livewire.buck-eye-game');
    }
}
