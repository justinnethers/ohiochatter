<?php

namespace App\Services;

use App\Models\Puzzle;
use App\Models\User;
use App\Models\UserGameProgress;
use App\Models\UserGameStats;
use Illuminate\Support\Facades\Storage;

class PuzzleService
{
    const MAX_GUESSES = 5;

    const PIXELATION_LEVELS = 5;

    public function processGuess(User $user, string $guess): array
    {
        $puzzle = $this->getTodaysPuzzle();

        $progress = $this->getUserGameProgress($user);

        $progress->attempts++;
        $previousGuesses = $progress->previous_guesses ?? [];
        $previousGuesses[] = $guess;
        $progress->previous_guesses = $previousGuesses;

        // Check if the guess is correct (case-insensitive)
        $isCorrect = strtolower(trim($guess)) === strtolower(trim($puzzle->answer));

        if ($isCorrect) {
            $progress->solved = true;
            $progress->guesses_taken = $progress->attempts;
            $progress->completed_at = now();

            // Update user stats
            $stats = UserGameStats::getOrCreateForUser($user->id);
            $stats->updateAfterGame(true, $progress->attempts);
        } elseif ($progress->attempts >= self::MAX_GUESSES) {
            // Out of guesses
            $progress->completed_at = now();

            // Update user stats
            $stats = UserGameStats::getOrCreateForUser($user->id);
            $stats->updateAfterGame(false);
        }

        $progress->save();

        // Return the appropriate response
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

    public function getImage(Puzzle $puzzle): string
    {
        // For now, just return the original image URL
        // The pixelation effect is applied using CSS in the Livewire template
        return Storage::url($puzzle->image_path);
    }
}
