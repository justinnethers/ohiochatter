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

        $progress = $this->getUserGameProgress($user);

        $progress->attempts++;
        $progress->previous_guesses = array_merge($progress->previous_guesses ?? [], [$guess]);

        $isCorrect = strtolower(trim($guess)) === strtolower(trim($puzzle->answer));

        if ($isCorrect) {
            $progress->solved = true;
            $progress->guesses_taken = $progress->attempts;
            $progress->completed_at = now();

            $stats = UserGameStats::getOrCreateForUser($user->id);
            $stats->updateAfterGame(true, $progress->attempts);
        } elseif ($progress->attempts >= self::MAX_GUESSES) {
            $progress->completed_at = now();

            $stats = UserGameStats::getOrCreateForUser($user->id);
            $stats->updateAfterGame(false);
        }

        $progress->save();

        if ($isCorrect) {
            return [
                'status' => 'correct',
                'pixelation_level' => 0,  // Clear image
                'remaining_guesses' => self::MAX_GUESSES - $progress->attempts,
                'game_complete' => true,
                'game_won' => true
            ];
        } else {
            // Calculate pixelation level (starts at MAX_LEVEL and decreases with each guess)
            $pixelationLevel = self::PIXELATION_LEVELS - $progress->attempts;
            if ($pixelationLevel < 0) $pixelationLevel = 0;

            return [
                'status' => 'incorrect',
                'pixelation_level' => $pixelationLevel,
                'remaining_guesses' => self::MAX_GUESSES - $progress->attempts,
                'game_complete' => $progress->attempts >= self::MAX_GUESSES,
                'game_won' => false
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
