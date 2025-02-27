<?php

namespace App\Http\Controllers;

use App\Models\Puzzle;
use App\Models\UserGameStats;
use App\Services\PuzzleService;
use Illuminate\Http\Request;
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
        // This method is for authenticated users only
        $userStats = UserGameStats::getOrCreateForUser(Auth::id());

        // Get recent puzzles to show history
        $recentPuzzles = Puzzle::orderBy('publish_date', 'desc')
            ->take(10)
            ->get();

        // Get user progress for these puzzles
        $puzzleProgress = [];
        foreach ($recentPuzzles as $puzzle) {
            $progress = Auth::user()->gameProgress()
                ->where('puzzle_id', $puzzle->id)
                ->first();

            $puzzleProgress[$puzzle->id] = $progress;
        }

        return view('buckeye.stats', compact('userStats', 'recentPuzzles', 'puzzleProgress'));
    }
}
