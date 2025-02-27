<?php

namespace App\Services;

use App\Models\Puzzle;
use App\Models\User;
use App\Models\UserGameProgress;
use App\Models\UserGameStats;
use Illuminate\Support\Facades\Storage;

class PuzzleService
{
    /**
     * Maximum number of guesses allowed
     */
    const MAX_GUESSES = 5;

    /**
     * Number of pixelation levels
     */
    const PIXELATION_LEVELS = 5;

    /**
     * Get today's puzzle, or null if none is available
     *
     * @return Puzzle|null
     */
    public function getTodaysPuzzle()
    {
        return Puzzle::getTodaysPuzzle();
    }

    /**
     * Check if a user has already played today's puzzle
     *
     * @param User $user
     * @return bool
     */
    public function hasUserPlayedToday(User $user)
    {
        $puzzle = $this->getTodaysPuzzle();

        if (!$puzzle) {
            return false;
        }

        return UserGameProgress::where('user_id', $user->id)
            ->where('puzzle_id', $puzzle->id)
            ->exists();
    }

    /**
     * Get or create a user's game progress for today's puzzle
     *
     * @param User $user
     * @return UserGameProgress
     */
    public function getUserGameProgress(User $user)
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

    /**
     * Process a user's guess
     *
     * @param User $user
     * @param string $guess
     * @return array Result with status and message
     */
    public function processGuess(User $user, string $guess)
    {
        $puzzle = $this->getTodaysPuzzle();

        if (!$puzzle) {
            return [
                'status' => 'error',
                'message' => 'No puzzle available for today.'
            ];
        }

        $progress = $this->getUserGameProgress($user);

        // Check if the game is already completed
        if ($progress->completed_at) {
            return [
                'status' => 'error',
                'message' => 'You have already completed today\'s puzzle.'
            ];
        }

        // Check if max guesses reached
        if ($progress->attempts >= self::MAX_GUESSES) {
            return [
                'status' => 'error',
                'message' => 'You have no more guesses left.'
            ];
        }

        // Record the attempt
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
                'message' => 'Congratulations! You guessed correctly!',
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
                'message' => 'Sorry, that\'s not correct.',
                'pixelation_level' => $pixelationLevel,
                'remaining_guesses' => self::MAX_GUESSES - $progress->attempts,
                'game_complete' => $progress->attempts >= self::MAX_GUESSES,
                'game_won' => false
            ];
        }
    }

    /**
     * Get pixelated image for the current puzzle at specified level
     *
     * @param Puzzle $puzzle
     * @param int $level (0 = clear, 5 = most pixelated)
     * @return string URL to the pixelated image
     */
    public function getPixelatedImage(Puzzle $puzzle, int $level)
    {
        // For now, just return the original image URL
        // The pixelation effect is applied using CSS in the Livewire template
        return Storage::url($puzzle->image_path);
    }
}
