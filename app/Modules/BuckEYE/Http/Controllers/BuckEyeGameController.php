<?php

namespace App\Modules\BuckEYE\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\BuckEYE\Models\Puzzle;
use App\Modules\BuckEYE\Models\UserGameStats;
use App\Modules\BuckEYE\Services\PuzzleService;
use App\Services\SeoService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BuckEyeGameController extends Controller
{
    public function __construct(
        private SeoService $seoService
    ) {}

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
        $seo = $this->seoService->forBuckEyeGame();

        return view('buckeye.index', compact('userStats', 'puzzle', 'seo'));
    }

    /**
     * Display the game for guest users
     */
    public function guestPlay(PuzzleService $puzzleService)
    {
        // Get today's puzzle
        $puzzle = $puzzleService->getTodaysPuzzle();
        $seo = $this->seoService->forBuckEyeGame();

        return view('buckeye.guest', compact('puzzle', 'seo'));
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
            'puzzleProgress' => $progress,
            'seo' => $this->seoService->forBuckEyeGame(),
        ]);
    }

    public function socialImage($date)
    {
        try {
            // Find the puzzle for the given date
            $puzzle = Puzzle::where('publish_date', $date)->firstOrFail();

            // Check if we already have a cached version of this social image
            $socialImagePath = 'social/' . $date . '.jpg';

            // If file exists, serve it directly
            if (Storage::disk('public')->exists($socialImagePath)) {
                return response()->file(Storage::disk('public')->path($socialImagePath));
            }

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

            // Efficient blur: scale down, apply built-in blur filter, scale back up
            // This is much faster than manual pixel-by-pixel processing
            $scaleFactor = 0.05;
            $smallWidth = max(1, (int) floor($width * $scaleFactor));
            $smallHeight = max(1, (int) floor($height * $scaleFactor));

            // Create small image (pixelation effect from downscaling)
            $small = imagecreatetruecolor($smallWidth, $smallHeight);
            imagecopyresampled($small, $source, 0, 0, 0, 0, $smallWidth, $smallHeight, $width, $height);

            // Apply PHP's built-in Gaussian blur multiple times for strong effect
            for ($i = 0; $i < 5; $i++) {
                imagefilter($small, IMG_FILTER_GAUSSIAN_BLUR);
            }

            // Scale back up to full size (creates pixelated blur effect)
            $blurred = imagecreatetruecolor($width, $height);
            imagecopyresampled($blurred, $small, 0, 0, 0, 0, $width, $height, $smallWidth, $smallHeight);

            // Apply one more blur at full size to smooth out pixelation edges
            imagefilter($blurred, IMG_FILTER_GAUSSIAN_BLUR);

            // Make sure the directory exists
            Storage::disk('public')->makeDirectory(dirname($socialImagePath), 0755, true, true);

            // Save the image to a file
            $savePath = Storage::disk('public')->path($socialImagePath);
            imagejpeg($blurred, $savePath, 90);

            // Clean up
            imagedestroy($source);
            imagedestroy($small);
            imagedestroy($blurred);

            // Return the saved file
            return response()->file($savePath);

        } catch (\Exception $e) {
            \Log::error('Social image error: ' . $e->getMessage());
            return response("Error generating image: " . $e->getMessage(), 500);
        }
    }
}
