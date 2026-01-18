<?php

namespace App\Modules\OhioWordle\Services;

use App\Models\User;
use App\Modules\OhioWordle\Models\WordioRejectedGuess;
use App\Modules\OhioWordle\Models\WordleAnonymousProgress;
use App\Modules\OhioWordle\Models\WordleUserProgress;
use App\Modules\OhioWordle\Models\WordleUserStats;
use App\Modules\OhioWordle\Models\WordleWord;
use Illuminate\Support\Facades\Cache;

class WordleService
{
    public const MAX_GUESSES = 6;

    private const CACHE_TTL_WORD = 60 * 60; // 1 hour
    private const CACHE_TTL_STATS = 60 * 5; // 5 minutes

    public function __construct(
        private DictionaryService $dictionaryService
    ) {}

    /**
     * Get today's word.
     */
    public function getTodaysWord(): ?WordleWord
    {
        return Cache::remember('todays_wordle_word', self::CACHE_TTL_WORD, function () {
            return WordleWord::getTodaysWord();
        });
    }

    /**
     * Get or create user progress for today's word.
     */
    public function getUserProgress(User $user): ?WordleUserProgress
    {
        $word = $this->getTodaysWord();

        if (! $word) {
            return null;
        }

        return WordleUserProgress::firstOrCreate(
            [
                'user_id' => $user->id,
                'word_id' => $word->id,
            ],
            [
                'solved' => false,
                'attempts' => 0,
                'guesses' => [],
                'feedback' => [],
            ]
        );
    }

    /**
     * Get guest progress for today's word.
     */
    public function getGuestProgress(): ?WordleAnonymousProgress
    {
        $word = $this->getTodaysWord();

        if (! $word) {
            return null;
        }

        $sessionId = session()->getId();

        return WordleAnonymousProgress::where('word_id', $word->id)
            ->where('session_id', $sessionId)
            ->first();
    }

    /**
     * Process a guess for an authenticated user.
     */
    public function processGuess(?User $user, string $guess): array
    {
        $word = $this->getTodaysWord();

        if (! $word) {
            return $this->errorResult('No puzzle available today');
        }

        // Validate the guess
        $validation = $this->validateGuess($guess, $word);
        if (! $validation['valid']) {
            $this->logRejectedGuess(
                $guess,
                $validation['reason'],
                $word->id,
                $user?->id,
                $user ? null : session()->getId()
            );

            return $validation;
        }

        $guess = strtoupper(trim($guess));

        // Get or create progress
        if ($user) {
            $progress = $this->getUserProgress($user);
        } else {
            $progress = $this->getOrCreateGuestProgress($word);
        }

        // Check if game is already complete
        if ($progress->completed_at) {
            return $this->errorResult('Game already completed');
        }

        // Check if word was already guessed
        if (in_array($guess, $progress->guesses ?? [], true)) {
            return $this->errorResult('You already guessed that word');
        }

        // Calculate feedback
        $feedback = $this->calculateFeedback($guess, $word->word);

        // Update progress
        $progress->addGuess($guess, $feedback);

        // Check if correct
        $isCorrect = $word->isCorrectGuess($guess);
        $isGameOver = $isCorrect || $progress->attempts >= self::MAX_GUESSES;

        if ($isGameOver) {
            $progress->complete($isCorrect);

            // Update user stats if authenticated
            if ($user) {
                $stats = WordleUserStats::getOrCreateForUser($user->id);
                $stats->updateAfterGame($isCorrect, $isCorrect ? $progress->attempts : null);
            }

            // Clear word stats cache so the new completion is reflected
            Cache::forget("wordle_stats_{$word->id}");
        }

        $progress->save();

        return [
            'valid' => true,
            'feedback' => $feedback,
            'previousGuesses' => $progress->guesses,
            'allFeedback' => $progress->feedback,
            'remainingGuesses' => self::MAX_GUESSES - $progress->attempts,
            'gameComplete' => $isGameOver,
            'gameWon' => $isCorrect,
            'answer' => $isGameOver ? $word->word : null,
        ];
    }

    /**
     * Validate a guess before processing.
     */
    private function validateGuess(string $guess, WordleWord $word): array
    {
        $guess = trim($guess);

        if (empty($guess)) {
            return $this->errorResult('Please enter a guess', WordioRejectedGuess::REASON_EMPTY);
        }

        $wordLength = $word->word_length;

        if (strlen($guess) !== $wordLength) {
            return $this->errorResult("Guess must be {$wordLength} letters", WordioRejectedGuess::REASON_WRONG_LENGTH);
        }

        if (! $this->dictionaryService->isValidWord($guess, $wordLength)) {
            return $this->errorResult("'{$guess}' is not a valid word", WordioRejectedGuess::REASON_NOT_IN_DICTIONARY);
        }

        return ['valid' => true];
    }

    /**
     * Get or create guest progress.
     */
    private function getOrCreateGuestProgress(WordleWord $word): WordleAnonymousProgress
    {
        $sessionId = session()->getId();

        return WordleAnonymousProgress::firstOrCreate(
            [
                'word_id' => $word->id,
                'session_id' => $sessionId,
            ],
            [
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'solved' => false,
                'attempts' => 0,
                'guesses' => [],
                'feedback' => [],
            ]
        );
    }

    /**
     * Create an error result.
     */
    private function errorResult(string $message, ?string $reason = null): array
    {
        $result = [
            'valid' => false,
            'error' => $message,
        ];

        if ($reason) {
            $result['reason'] = $reason;
        }

        return $result;
    }

    /**
     * Log a rejected guess attempt.
     */
    private function logRejectedGuess(
        string $guess,
        string $reason,
        ?int $wordId,
        ?int $userId,
        ?string $sessionId
    ): void {
        WordioRejectedGuess::log($guess, $reason, $wordId, $userId, $sessionId);
    }

    /**
     * Calculate feedback for a guess against an answer.
     * Returns an array of 'correct', 'present', or 'absent' for each letter.
     */
    public function calculateFeedback(string $guess, string $answer): array
    {
        $guess = strtoupper($guess);
        $answer = strtoupper($answer);

        $guessLetters = str_split($guess);
        $answerLetters = str_split($answer);
        $length = count($guessLetters);

        if ($length === 0) {
            return [];
        }

        // Initialize feedback array and track available letters
        $feedback = array_fill(0, $length, 'absent');
        $availableLetters = array_count_values($answerLetters);

        // First pass: Mark correct positions
        for ($i = 0; $i < $length; $i++) {
            if ($guessLetters[$i] === $answerLetters[$i]) {
                $feedback[$i] = 'correct';
                $availableLetters[$guessLetters[$i]]--;
            }
        }

        // Second pass: Mark present (wrong position but in word)
        for ($i = 0; $i < $length; $i++) {
            if ($feedback[$i] === 'correct') {
                continue;
            }

            $letter = $guessLetters[$i];
            if (isset($availableLetters[$letter]) && $availableLetters[$letter] > 0) {
                $feedback[$i] = 'present';
                $availableLetters[$letter]--;
            }
        }

        return $feedback;
    }

    /**
     * Load statistics for a word.
     */
    public function loadWordStats(WordleWord $word): array
    {
        return Cache::remember("wordle_stats_{$word->id}", self::CACHE_TTL_STATS, function () use ($word) {
            // Get authenticated user progress
            $userProgress = WordleUserProgress::where('word_id', $word->id)->get();

            // Get anonymous progress
            $anonymousProgress = WordleAnonymousProgress::where('word_id', $word->id)->get();

            $totalPlayers = $userProgress->count() + $anonymousProgress->count();

            $solvedUsers = $userProgress->where('solved', true);
            $solvedAnonymous = $anonymousProgress->where('solved', true);
            $solvedCount = $solvedUsers->count() + $solvedAnonymous->count();

            $completionRate = $totalPlayers > 0 ? round(($solvedCount / $totalPlayers) * 100) : 0;

            // Calculate average guesses
            $allGuesses = $solvedUsers->pluck('guesses_taken')
                ->merge($solvedAnonymous->pluck('guesses_taken'))
                ->filter();

            $averageGuesses = $allGuesses->count() > 0 ? round($allGuesses->avg(), 1) : 'N/A';

            // Calculate guess distribution
            $distribution = [];
            for ($i = 1; $i <= self::MAX_GUESSES; $i++) {
                $distribution[$i] = $solvedUsers->where('guesses_taken', $i)->count()
                    + $solvedAnonymous->where('guesses_taken', $i)->count();
            }

            return [
                'totalPlayers' => $totalPlayers,
                'solvedCount' => $solvedCount,
                'completionRate' => $completionRate,
                'averageGuesses' => $averageGuesses,
                'guessDistribution' => $distribution,
            ];
        });
    }
}
