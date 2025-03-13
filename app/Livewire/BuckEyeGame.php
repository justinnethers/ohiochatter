<?php

namespace App\Livewire;

use App\Models\UserGameStats;
use App\Services\PuzzleService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
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
     * Stats for the current puzzle
     */
    public $puzzleStats = null;

    /**
     * Whether to show puzzle stats (only after completing the game)
     */
    public $showPuzzleStats = false;

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

                $this->gameComplete = (bool)$progress->completed_at;
                $this->gameWon = $progress->solved;

                // Load stats if the game is already complete
                if ($this->gameComplete) {
                    $this->loadPuzzleStats();
                    $this->showPuzzleStats = true;
                }

                // Load user stats
                $this->userStats = UserGameStats::getOrCreateForUser(Auth::id());
            }
        } else {
            // For guests, load from session
            $sessionKey = 'guest_game_' . $this->puzzle->publish_date;
            $guestData = Session::get($sessionKey);

            if ($guestData) {
                $this->previousGuesses = $guestData['previousGuesses'] ?? [];
                $this->remainingGuesses = $guestData['remainingGuesses'] ?? 5;
                $this->pixelationLevel = $guestData['pixelationLevel'] ?? 5;
                $this->gameComplete = $guestData['gameComplete'] ?? false;
                $this->gameWon = $guestData['gameWon'] ?? false;

                // Load stats if the game is already complete
                if ($this->gameComplete) {
                    $this->loadPuzzleStats();
                    $this->showPuzzleStats = true;
                }

                // Set appropriate messages if the game is complete
                if ($this->gameComplete) {
                    if ($this->gameWon) {
                        $this->successMessage = "Congratulations! You guessed correctly!";
                    } else {
                        $this->errorMessage = "Sorry, you're out of guesses. The answer was: " . $this->puzzle->answer;
                    }
                }
            }
        }

        // Get the image
        $this->imageUrl = $puzzleService->getPixelatedImage($this->puzzle, $this->pixelationLevel);
    }

    /**
     * Load statistics for the current puzzle
     */
    public function loadPuzzleStats()
    {
        if (!$this->puzzle) {
            return;
        }

        // Get total players
        $totalPlayers = \App\Models\UserGameProgress::where('puzzle_id', $this->puzzle->id)->count();

        // Get solved count
        $solvedCount = \App\Models\UserGameProgress::where('puzzle_id', $this->puzzle->id)
            ->where('solved', true)
            ->count();

        // Calculate completion rate
        $completionRate = $totalPlayers > 0
            ? round(($solvedCount / $totalPlayers) * 100)
            : 0;

        // Get average guesses for solved puzzles
        $averageGuesses = \App\Models\UserGameProgress::where('puzzle_id', $this->puzzle->id)
            ->where('solved', true)
            ->avg('guesses_taken');

        $averageGuesses = $averageGuesses ? round($averageGuesses, 1) : 'N/A';

        // Get distribution of guesses
        $guessDistribution = \App\Models\UserGameProgress::where('puzzle_id', $this->puzzle->id)
            ->where('solved', true)
            ->groupBy('guesses_taken')
            ->select('guesses_taken', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->orderBy('guesses_taken')
            ->pluck('count', 'guesses_taken')
            ->toArray();

        // Store stats in the property
        $this->puzzleStats = [
            'totalPlayers' => $totalPlayers,
            'solvedCount' => $solvedCount,
            'completionRate' => $completionRate,
            'averageGuesses' => $averageGuesses,
            'guessDistribution' => $guessDistribution
        ];
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

        // Clear any existing messages
        $this->errorMessage = null;
        $this->successMessage = null;

        if (Auth::check()) {
            // For authenticated users - existing code
            $result = $puzzleService->processGuess(Auth::user(), $currentGuess);

            if ($result['status'] === 'error') {
                $this->errorMessage = $result['message'];
                return;
            }

            $this->previousGuesses[] = $currentGuess;
            $this->remainingGuesses = $result['remaining_guesses'];
            $this->pixelationLevel = $result['pixelation_level'];
            $this->gameComplete = $result['game_complete'];
            $this->gameWon = $result['game_won'];

            if ($this->gameWon) {
                $this->pixelationLevel = 0;
            }

            if ($result['status'] === 'correct') {
                $this->successMessage = $result['message'];
            } else {
                $this->errorMessage = $result['message'];
            }

            if ($this->gameComplete && Auth::check()) {
                $this->userStats = UserGameStats::getOrCreateForUser(Auth::id());
                // Load puzzle stats for the completed game
                $this->loadPuzzleStats();
                $this->showPuzzleStats = true;
                $this->dispatch('gameCompleted', [
                    'won' => $this->gameWon,
                    'guesses' => count($this->previousGuesses)
                ]);
            }
        } else {
            // For guest users
            $isCorrect = strtolower(trim($currentGuess)) === strtolower(trim($this->puzzle->answer));
            $this->previousGuesses[] = $currentGuess;
            $this->remainingGuesses--;
            $this->pixelationLevel = max(0, PuzzleService::PIXELATION_LEVELS - count($this->previousGuesses));

            if ($isCorrect) {
                $this->gameComplete = true;
                $this->gameWon = true;
                $this->pixelationLevel = 0;
                $this->successMessage = "Congratulations! You guessed correctly!";
                // Load stats for the completed game
                $this->loadPuzzleStats();
                $this->showPuzzleStats = true;
            } else if ($this->remainingGuesses <= 0) {
                $this->gameComplete = true;
                $this->gameWon = false;
                $this->pixelationLevel = 0;
                $this->errorMessage = "Sorry, you're out of guesses. The answer was: " . $this->puzzle->answer;
                // Load stats for the completed game
                $this->loadPuzzleStats();
                $this->showPuzzleStats = true;
            } else {
                $this->errorMessage = "Sorry, that's not correct. Try again!";
            }

            // Save to session
            $sessionKey = 'guest_game_' . $this->puzzle->publish_date;
            Session::put($sessionKey, [
                'previousGuesses' => $this->previousGuesses,
                'remainingGuesses' => $this->remainingGuesses,
                'pixelationLevel' => $this->pixelationLevel,
                'gameComplete' => $this->gameComplete,
                'gameWon' => $this->gameWon,
                'showPuzzleStats' => $this->showPuzzleStats
            ]);
        }

        // Update the image URL
        $this->imageUrl = $puzzleService->getPixelatedImage($this->puzzle, $this->pixelationLevel);

        $this->currentGuess = '';
        $this->dispatch('clearCurrentGuess');
    }

    public function render()
    {
        return view('livewire.buck-eye-game');
    }
}
