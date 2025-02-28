<?php

namespace App\Http\Controllers;

use App\Models\Puzzle;
use App\Models\UserGameStats;
use App\Services\PuzzleService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

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

            $todayCompleted = (bool) $todayProgress;
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
        $playedPuzzles = $recentPuzzles->filter(function($puzzle) use ($progress) {
            return isset($progress[$puzzle->id]);
        });

        return view('buckeye.stats', [
            'userStats' => $userStats,
            'recentPuzzles' => $playedPuzzles,
            'puzzleProgress' => $progress
        ]);
    }
}
