<?php

namespace App\Services;

use App\Models\AnonymousGameProgress;
use App\Models\Puzzle;
use App\Models\User;
use App\Models\UserGameProgress;
use App\Models\UserGameStats;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class PuzzleService
{
    const MAX_GUESSES = 5;

    const PIXELATION_LEVELS = 5;

    public function loadPuzzleStats(Puzzle $puzzle): array
    {
        $authenticatedQuery = UserGameProgress::where('puzzle_id', $puzzle->id);
        $anonymousQuery = AnonymousGameProgress::where('puzzle_id', $puzzle->id);

        $totalAuthPlayers = $authenticatedQuery->count();
        $totalAnonPlayers = $anonymousQuery->count();
        $totalPlayers = $totalAuthPlayers + $totalAnonPlayers;

        $solvedAuthCount = UserGameProgress::where('puzzle_id', $puzzle->id)
            ->where('solved', true)
            ->count();

        $solvedAnonCount = AnonymousGameProgress::where('puzzle_id', $puzzle->id)
            ->where('solved', true)
            ->count();

        $solvedCount = $solvedAuthCount + $solvedAnonCount;

        $completionRate = $totalPlayers > 0
            ? round(($solvedCount / $totalPlayers) * 100)
            : 0;

        $authAvgGuesses = UserGameProgress::where('puzzle_id', $puzzle->id)
            ->where('solved', true)
            ->avg('guesses_taken');

        $anonAvgGuesses = AnonymousGameProgress::where('puzzle_id', $puzzle->id)
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

        $authDistribution = UserGameProgress::where('puzzle_id', $puzzle->id)
            ->where('solved', true)
            ->groupBy('guesses_taken')
            ->select('guesses_taken', DB::raw('count(*) as count'))
            ->pluck('count', 'guesses_taken')
            ->toArray();

        $anonDistribution = AnonymousGameProgress::where('puzzle_id', $puzzle->id)
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

        return [
            'totalPlayers' => $totalPlayers,
            'solvedCount' => $solvedCount,
            'completionRate' => $completionRate,
            'averageGuesses' => $averageGuesses,
            'guessDistribution' => $guessDistribution
        ];
    }

    public function processGuess(User $user, string $guess): array
    {
        $puzzle = $this->getTodaysPuzzle();
        $userGameProgress = $this->getUserGameProgress($user);

        $userGameProgress->attempts++;
        $userGameProgress->previous_guesses = array_merge(
            $userGameProgress->previous_guesses ?? [],
            [$guess]
        );

        $isCorrect = $puzzle->isCorrectAnswer($guess);

        if ($isCorrect) {
            $userGameProgress->solved = true;
            $userGameProgress->guesses_taken = $userGameProgress->attempts;
            $userGameProgress->completed_at = now();

            $stats = UserGameStats::getOrCreateForUser($user->id);
            $stats->updateAfterGame(true, $userGameProgress->attempts);
        } elseif ($userGameProgress->attempts >= self::MAX_GUESSES) {
            $userGameProgress->completed_at = now();

            $stats = UserGameStats::getOrCreateForUser($user->id);
            $stats->updateAfterGame(false);
        }

        $userGameProgress->save();

        if ($isCorrect) {
            return [
                'pixelationLevel' => 0,
                'remainingGuesses' => self::MAX_GUESSES - $userGameProgress->attempts,
                'previousGuesses' => $userGameProgress->previous_guesses,
                'gameComplete' => true,
                'gameWon' => true
            ];
        } else {
            // Calculate pixelation level (starts at MAX_LEVEL and decreases with each guess)
            $pixelationLevel = self::PIXELATION_LEVELS - $userGameProgress->attempts;
            if ($pixelationLevel < 0) $pixelationLevel = 0;

            return [
                'pixelationLevel' => $pixelationLevel,
                'remainingGuesses' => self::MAX_GUESSES - $userGameProgress->attempts,
                'previousGuesses' => $userGameProgress->previous_guesses,
                'gameComplete' => $userGameProgress->attempts >= self::MAX_GUESSES,
                'gameWon' => false
            ];
        }
    }

    public function getTodaysPuzzle(): ?Puzzle
    {
        return Puzzle::getTodaysPuzzle();
    }

    public function getUserGameProgress(User $user): UserGameProgress|null
    {
        $puzzle = $this->getTodaysPuzzle();

        if (!$puzzle) {
            return null;
        }

        return UserGameProgress::firstOrCreate([
            'user_id' => $user->id,
            'puzzle_id' => $puzzle->id,
        ]);
    }

    public function getGuestGameProgress(): AnonymousGameProgress|null
    {
        $puzzle = $this->getTodaysPuzzle();

        if (!$puzzle) {
            return null;
        }

        $sessionId = Session::getId();
        return AnonymousGameProgress::where('puzzle_id', $puzzle->id)
            ->where('session_id', $sessionId)
            ->first();
    }

    public function getImage(Puzzle $puzzle): string
    {
        // For now, just return the original image URL
        // The pixelation effect is applied using CSS in the Livewire template
        return Storage::url($puzzle->image_path);
    }
}
