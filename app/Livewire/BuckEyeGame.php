<?php

namespace App\Livewire;

use App\Models\AnonymousGameProgress;
use App\Models\UserGameProgress;
use App\Models\UserGameStats;
use App\Services\PuzzleService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

class BuckEyeGame extends Component
{
    public $puzzle;

    public $currentGuess = '';

    public $previousGuesses = [];

    public $remainingGuesses = PuzzleService::MAX_GUESSES;

    public $pixelationLevel = PuzzleService::PIXELATION_LEVELS;

    public $gameComplete = false;

    public $gameWon = false;

    public $imageUrl;

    public $wordCount;

    public $userStats;

    public $errorMessage;

    public $puzzleStats = null;

    public $showPuzzleStats = false;

    protected $rules = [
        'currentGuess' => 'required|string|min:2',
    ];

    public function mount(PuzzleService $puzzleService)
    {
        $this->puzzle = $puzzleService->getTodaysPuzzle();

        if (!$this->puzzle) {
            $this->errorMessage = "No puzzle available for today.";
            return;
        }

        $this->wordCount = $this->puzzle->word_count;
        $this->imageUrl = $puzzleService->getImage($this->puzzle);

        if (Auth::check()) {
            $progress = $puzzleService->getUserGameProgress(Auth::user());

            if ($progress) {
                $this->getUserProgress($progress);
                $this->userStats = UserGameStats::getOrCreateForUser(Auth::id());
            }
        } else {
            $sessionId = Session::getId();
            $anonymousProgress = AnonymousGameProgress::where('puzzle_id', $this->puzzle->id)
                ->where('session_id', $sessionId)
                ->first();

            if ($anonymousProgress) {
                $this->getUserProgress($anonymousProgress);
            }
        }
    }

    /**
     * @param UserGameProgress|AnonymousGameProgress $progress
     * @return void
     */
    private function getUserProgress(UserGameProgress|AnonymousGameProgress $progress): void
    {
        $this->previousGuesses = $progress->previous_guesses ?: [];
        $this->remainingGuesses = PuzzleService::MAX_GUESSES - $progress->attempts;
        $this->pixelationLevel = PuzzleService::PIXELATION_LEVELS - $progress->attempts;

        if ($progress->solved || $this->pixelationLevel <= 0) {
            $this->pixelationLevel = 0;
        }

        $this->gameComplete = (bool)$progress->completed_at;
        $this->gameWon = $progress->solved;

        if ($this->gameComplete) {
            $this->loadPuzzleStats();
        }
    }

    /**
     * Load statistics for the current puzzle
     */
    public function loadPuzzleStats(): void
    {
        if (!$this->puzzle) {
            return;
        }

        $authenticatedQuery = UserGameProgress::where('puzzle_id', $this->puzzle->id);
        $anonymousQuery = AnonymousGameProgress::where('puzzle_id', $this->puzzle->id);

        $totalAuthPlayers = $authenticatedQuery->count();
        $totalAnonPlayers = $anonymousQuery->count();
        $totalPlayers = $totalAuthPlayers + $totalAnonPlayers;

        $solvedAuthCount = UserGameProgress::where('puzzle_id', $this->puzzle->id)
            ->where('solved', true)
            ->count();

        $solvedAnonCount = AnonymousGameProgress::where('puzzle_id', $this->puzzle->id)
            ->where('solved', true)
            ->count();

        $solvedCount = $solvedAuthCount + $solvedAnonCount;

        $completionRate = $totalPlayers > 0
            ? round(($solvedCount / $totalPlayers) * 100)
            : 0;

        $authAvgGuesses = UserGameProgress::where('puzzle_id', $this->puzzle->id)
            ->where('solved', true)
            ->avg('guesses_taken');

        $anonAvgGuesses = AnonymousGameProgress::where('puzzle_id', $this->puzzle->id)
            ->where('solved', true)
            ->avg('guesses_taken');

        $avgGuesses = null;
        if ($solvedAuthCount > 0 || $solvedAnonCount > 0) {
            $totalGuesses = 0;
            $totalSolved = 0;

            if ($solvedAuthCount > 0 && $authAvgGuesses) {
                $totalGuesses += $authAvgGuesses * $solvedAuthCount;
                $totalSolved += $solvedAuthCount;
            }

            if ($solvedAnonCount > 0 && $anonAvgGuesses) {
                $totalGuesses += $anonAvgGuesses * $solvedAnonCount;
                $totalSolved += $solvedAnonCount;
            }

            $avgGuesses = $totalSolved > 0 ? round($totalGuesses / $totalSolved, 1) : null;
        }

        $averageGuesses = $avgGuesses ?: 'N/A';

        $authDistribution = UserGameProgress::where('puzzle_id', $this->puzzle->id)
            ->where('solved', true)
            ->groupBy('guesses_taken')
            ->select('guesses_taken', DB::raw('count(*) as count'))
            ->pluck('count', 'guesses_taken')
            ->toArray();

        $anonDistribution = AnonymousGameProgress::where('puzzle_id', $this->puzzle->id)
            ->where('solved', true)
            ->groupBy('guesses_taken')
            ->select('guesses_taken', DB::raw('count(*) as count'))
            ->pluck('count', 'guesses_taken')
            ->toArray();

        $guessDistribution = [];
        foreach (range(1, 5) as $guessNum) {
            $authCount = $authDistribution[$guessNum] ?? 0;
            $anonCount = $anonDistribution[$guessNum] ?? 0;
            $guessDistribution[$guessNum] = $authCount + $anonCount;
        }

        $this->puzzleStats = [
            'totalPlayers' => $totalPlayers,
            'solvedCount' => $solvedCount,
            'completionRate' => $completionRate,
            'averageGuesses' => $averageGuesses,
            'guessDistribution' => $guessDistribution
        ];

        $this->showPuzzleStats = true;
    }

    public function submitGuess(PuzzleService $puzzleService): void
    {
        if ($this->gameComplete) {
            $this->errorMessage = "This game is already complete.";
            return;
        }

        $this->validate();

        $currentGuess = $this->currentGuess;

        $this->errorMessage = null;

        if (Auth::check()) {
            $result = $puzzleService->processGuess(Auth::user(), $currentGuess);

            $this->previousGuesses[] = $currentGuess;
            $this->remainingGuesses = $result['remaining_guesses'];
            $this->pixelationLevel = $result['pixelation_level'];
            $this->gameComplete = $result['game_complete'];
            $this->gameWon = $result['game_won'];

            if ($result['status'] === 'incorrect') {
                $this->errorMessage = 'Not quite. Try again!';
            }

            if ($this->gameComplete) {
                $this->pixelationLevel = 0;
                $this->userStats = UserGameStats::getOrCreateForUser(Auth::id());

                $this->dispatch('gameCompleted', [
                    'won' => $this->gameWon,
                    'guesses' => count($this->previousGuesses)
                ]);

                $this->loadPuzzleStats();
            }
        } else {
            $isCorrect = strtolower(trim($currentGuess)) === strtolower(trim($this->puzzle->answer));
            $this->previousGuesses[] = $currentGuess;
            $this->remainingGuesses--;
            $this->pixelationLevel = max(0, PuzzleService::PIXELATION_LEVELS - count($this->previousGuesses));

            if ($isCorrect || $this->remainingGuesses <= 0) {
                $this->gameComplete = true;
                $this->gameWon = $isCorrect;
                $this->pixelationLevel = 0;

                $this->saveAnonymousProgress();

                $this->loadPuzzleStats();
            } else {
                $this->saveAnonymousProgress();
                $this->errorMessage = "Not quite. Try again!";
            }
        }

        $this->currentGuess = '';
        $this->dispatch('clearCurrentGuess');
    }

    /**
     * Save anonymous user progress to the database and session
     */
    private function saveAnonymousProgress(): void
    {
        $sessionId = Session::getId();
        $ipAddress = request()->ip();
        $userAgent = request()->userAgent();

        AnonymousGameProgress::updateOrCreate(
            [
                'puzzle_id' => $this->puzzle->id,
                'session_id' => $sessionId
            ],
            [
                'solved' => $this->gameWon,
                'attempts' => count($this->previousGuesses),
                'guesses_taken' => $this->gameWon ? count($this->previousGuesses) : null,
                'previous_guesses' => $this->previousGuesses,
                'completed_at' => $this->gameComplete ? now() : null,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent
            ]
        );

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

    public function render()
    {
        return view('livewire.buck-eye-game');
    }
}
