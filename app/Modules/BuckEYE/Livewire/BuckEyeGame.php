<?php

namespace App\Modules\BuckEYE\Livewire;

use App\Modules\BuckEYE\Models\AnonymousGameProgress;
use App\Modules\BuckEYE\Models\UserGameProgress;
use App\Modules\BuckEYE\Models\UserGameStats;
use App\Modules\BuckEYE\Services\PuzzleService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class BuckEyeGame extends Component
{
    public $puzzle;

    public $gameState = [
        'previousGuesses' => [],
        'remainingGuesses' => PuzzleService::MAX_GUESSES,
        'pixelationLevel' => PuzzleService::PIXELATION_LEVELS,
        'gameComplete' => false,
        'gameWon' => false,
    ];

    public $currentGuess;

    public $imageUrl;

    public $userStats;

    public $errorMessage;

    public $puzzleStats = null;

    public $showPuzzleStats = false;

    protected $rules = [
        'currentGuess' => 'required|string|min:1',
    ];

    public function mount(PuzzleService $puzzleService)
    {
        $this->puzzle = $puzzleService->getTodaysPuzzle();

        if (!$this->puzzle) {
            $this->errorMessage = "No puzzle available for today.";
            return;
        }

        $this->imageUrl = $puzzleService->getImage($this->puzzle);

        if (Auth::check()) {
            $progress = $puzzleService->getUserGameProgress(Auth::user());

            if ($progress) {
                $this->getUserProgress($progress);
                $this->userStats = UserGameStats::getOrCreateForUser(Auth::id());
            }
        } else {
            $guestProgress = $puzzleService->getGuestGameProgress();

            if ($guestProgress) {
                $this->getUserProgress($guestProgress);
            }
        }
    }

    private function getUserProgress(UserGameProgress|AnonymousGameProgress $progress): void
    {
        $this->gameState['previousGuesses'] = $progress->previous_guesses ?: [];
        $this->gameState['remainingGuesses'] = PuzzleService::MAX_GUESSES - $progress->attempts;
        $this->gameState['pixelationLevel'] = PuzzleService::PIXELATION_LEVELS - $progress->attempts;

        $this->gameState['gameComplete'] = (bool)$progress->completed_at;
        $this->gameState['gameWon'] = $progress->solved;

        if ($this->gameState['gameComplete']) {
            $this->gameState['pixelationLevel'] = 0;
            $this->showPuzzleStats();
        }
    }

    public function showPuzzleStats(): void
    {
        $puzzleService = app(PuzzleService::class);
        $this->puzzleStats = $puzzleService->loadPuzzleStats($this->puzzle);
        $this->showPuzzleStats = true;
    }

    public function submitGuess(PuzzleService $puzzleService): void
    {
        if ($this->gameState['gameComplete']) {
            $this->errorMessage = "This game is already complete.";
            return;
        }

        $this->validate();

        $this->errorMessage = null;

        if (Auth::check()) {
            $result = $puzzleService->processGuess(Auth::user(), $this->currentGuess);

            $this->gameState = array_merge($this->gameState, $result);

            if ($this->gameState['gameComplete']) {
                $this->userStats = UserGameStats::getOrCreateForUser(Auth::id());

                $this->dispatch('gameCompleted', [
                    'won' => $this->gameState['gameWon'],
                    'guesses' => count($this->gameState['previousGuesses'])
                ]);

                $this->showPuzzleStats();
            } else {
                $this->errorMessage = 'Not quite. Try again!';
            }

        } else {
            $isCorrect = $this->puzzle->isCorrectAnswer($this->currentGuess);

            $this->gameState['previousGuesses'][] = $this->currentGuess;
            $this->gameState['remainingGuesses']--;
            $this->gameState['pixelationLevel'] = max(0, PuzzleService::PIXELATION_LEVELS - count($this->gameState['previousGuesses']));

            if ($isCorrect || $this->gameState['remainingGuesses'] <= 0) {
                $this->gameState['gameComplete'] = true;
                $this->gameState['gameWon'] = $isCorrect;
                $this->gameState['pixelationLevel'] = 0;

                $this->saveAnonymousProgress();

                $this->showPuzzleStats();
            } else {
                $this->saveAnonymousProgress();
                $this->errorMessage = "Not quite. Try again!";
            }
        }

        $this->currentGuess = '';
        $this->dispatch('clearCurrentGuess');
    }

    private function saveAnonymousProgress(): void
    {
        AnonymousGameProgress::query()->updateOrCreate(
            [
                'puzzle_id' => $this->puzzle->id,
                'session_id' => Session::getId()
            ],
            [
                'solved' => $this->gameState['gameWon'],
                'attempts' => count($this->gameState['previousGuesses']),
                'guesses_taken' => $this->gameState['gameWon'] ? count($this->gameState['previousGuesses']) : null,
                'previous_guesses' => $this->gameState['previousGuesses'],
                'completed_at' => $this->gameState['gameComplete'] ? now() : null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]
        );

        if ($this->gameState['gameComplete']) {
            app(PuzzleService::class)->clearPuzzleStatsCache($this->puzzle);
        }
    }

    public function render()
    {
        return view('livewire.buck-eye-game');
    }
}
