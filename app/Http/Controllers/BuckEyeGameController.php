<?php

namespace App\Http\Controllers;

use App\Models\Puzzle;
use App\Models\UserGameStats;
use App\Services\PuzzleService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BuckEyeGameController extends Controller
{
    /**
     * Display the BuckEye game page for authenticated users
     */
    public function index(PuzzleService $puzzleService)
    {
        // Get statistics for the current user if authenticated
        $userStats = null;
        if (Auth::check()) {
            $userStats = UserGameStats::getOrCreateForUser(Auth::id());
        }

        // Get today's puzzle
        $puzzle = $puzzleService->getTodaysPuzzle();

        return view('buckeye.index', compact('userStats', 'puzzle'));
    }

    /**
     * Display the game for guest users
     */
    public function guestPlay(PuzzleService $puzzleService)
    {
        // Get today's puzzle
        $puzzle = $puzzleService->getTodaysPuzzle();

        return view('buckeye.guest', compact('puzzle'));
    }

    /**
     * Display user statistics page
     */
    public function stats()
    {
        $user = Auth::user();

        // This method is for authenticated users only
        $userStats = UserGameStats::getOrCreateForUser($user->id);

        // Get today's puzzle id if it exists
        $todaysPuzzle = Puzzle::where('publish_date', Carbon::today()->toDateString())->first();
        $todaysPuzzleId = $todaysPuzzle ? $todaysPuzzle->id : null;

        // Check if user has completed today's puzzle
        $todayCompleted = false;
        if ($todaysPuzzleId) {
            $todayProgress = $user->gameProgress()
                ->where('puzzle_id', $todaysPuzzleId)
                ->where('completed_at', '!=', null)
                ->first();

            $todayCompleted = (bool)$todayProgress;
        }

        // Build query for recent puzzles
        $puzzleQuery = Puzzle::query();

        if ($todayCompleted) {
            // Include today and past if today is completed
            $puzzleQuery->where('publish_date', '<=', Carbon::today()->toDateString());
        } else {
            // Only include past puzzles if today isn't completed
            $puzzleQuery->where('publish_date', '<', Carbon::today()->toDateString());
        }

        // Get the 5 most recent puzzles that match our criteria
        $recentPuzzles = $puzzleQuery->orderBy('publish_date', 'desc')
            ->take(5)
            ->get();

        // Get only the progress for puzzles that have been played
        $puzzleIds = $recentPuzzles->pluck('id')->toArray();
        $progress = $user->gameProgress()
            ->whereIn('puzzle_id', $puzzleIds)
            ->get()
            ->keyBy('puzzle_id');

        // Filter to only include puzzles that have been played
        $playedPuzzles = $recentPuzzles->filter(function ($puzzle) use ($progress) {
            return isset($progress[$puzzle->id]);
        });

        return view('buckeye.stats', [
            'userStats' => $userStats,
            'recentPuzzles' => $playedPuzzles,
            'puzzleProgress' => $progress
        ]);
    }

    public function socialImage($date)
    {
        try {
            // Find the puzzle for the given date
            $puzzle = Puzzle::where('publish_date', $date)->firstOrFail();

            // Get the original image path
            $originalPath = Storage::disk('public')->path($puzzle->image_path);

            // Get image information
            $info = getimagesize($originalPath);
            if (!$info) {
                throw new \Exception("Unable to get image info");
            }

            // Create source image based on file type
            $source = match ($info[2]) {
                IMAGETYPE_JPEG => imagecreatefromjpeg($originalPath),
                IMAGETYPE_PNG => imagecreatefrompng($originalPath),
                IMAGETYPE_WEBP => imagecreatefromwebp($originalPath),
                default => throw new \Exception("Unsupported image type"),
            };

            if (!$source) {
                throw new \Exception("Failed to create source image");
            }

            // Get dimensions
            $width = imagesx($source);
            $height = imagesy($source);

            // Create a blurred version using a more efficient approach
            // Scale down significantly first to reduce processing time
            $scaleFactor = 0.17; // Scale to 10% for processing
            $smallWidth = max(1, floor($width * $scaleFactor));
            $smallHeight = max(1, floor($height * $scaleFactor));

            // Create small image (reduces details)
            $small = imagecreatetruecolor($smallWidth, $smallHeight);
            imagecopyresampled($small, $source, 0, 0, 0, 0, $smallWidth, $smallHeight, $width, $height);

            // Apply a simple box blur to the small image (much faster)
            $blurredSmall = imagecreatetruecolor($smallWidth, $smallHeight);
            $blurRadius = 10; // A small radius is sufficient on the downsampled image

            // Simple box blur
            for ($y = 0; $y < $smallHeight; $y++) {
                for ($x = 0; $x < $smallWidth; $x++) {
                    $redTotal = $greenTotal = $blueTotal = 0;
                    $count = 0;

                    // Sample surrounding pixels
                    for ($j = max(0, $y - $blurRadius); $j <= min($smallHeight - 1, $y + $blurRadius); $j++) {
                        for ($i = max(0, $x - $blurRadius); $i <= min($smallWidth - 1, $x + $blurRadius); $i++) {
                            $rgb = imagecolorat($small, $i, $j);
                            $redTotal += ($rgb >> 16) & 0xFF;
                            $greenTotal += ($rgb >> 8) & 0xFF;
                            $blueTotal += $rgb & 0xFF;
                            $count++;
                        }
                    }

                    // Set the average color
                    $red = round($redTotal / $count);
                    $green = round($greenTotal / $count);
                    $blue = round($blueTotal / $count);

                    imagesetpixel($blurredSmall, $x, $y, imagecolorallocate($blurredSmall, $red, $green, $blue));
                }
            }

            // Scale back up to full size
            $blurred = imagecreatetruecolor($width, $height);
            imagecopyresampled($blurred, $blurredSmall, 0, 0, 0, 0, $width, $height, $smallWidth, $smallHeight);

            // Output image
            ob_start();
            imagejpeg($blurred, null, 90);
            $imageData = ob_get_clean();

            // Clean up
            imagedestroy($source);
            imagedestroy($small);
            imagedestroy($blurredSmall);
            imagedestroy($blurred);

            // Return response
            return response($imageData)
                ->header('Content-Type', 'image/jpeg')
                ->header('Cache-Control', 'public, max-age=86400');

        } catch (\Exception $e) {
            \Log::error('Social image error: ' . $e->getMessage());
            return response("Error generating image: " . $e->getMessage(), 500);
        }
    }
}
