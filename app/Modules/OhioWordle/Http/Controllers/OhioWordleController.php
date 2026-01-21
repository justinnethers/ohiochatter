<?php

namespace App\Modules\OhioWordle\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\OhioWordle\Models\WordleUserStats;
use App\Modules\OhioWordle\Models\WordleWord;
use App\Modules\OhioWordle\Services\WordioService;
use App\Services\SeoService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class OhioWordleController extends Controller
{
    public function __construct(
        private SeoService $seoService
    )
    {
    }

    /**
     * Display the OhioWordle game page for authenticated users.
     */
    public function index(WordioService $wordleService)
    {
        $userStats = null;
        if (Auth::check()) {
            $userStats = WordleUserStats::getOrCreateForUser(Auth::id());
        }

        $word = $wordleService->getTodaysWord();
        $seo = $this->seoService->forOhioWordle();

        return view('ohiowordle.index', compact('userStats', 'word', 'seo'));
    }

    /**
     * Display the game for guest users.
     */
    public function guestPlay(WordioService $wordleService)
    {
        $word = $wordleService->getTodaysWord();
        $seo = $this->seoService->forOhioWordle();

        return view('ohiowordle.index', [
            'word' => $word,
            'seo' => $seo,
            'userStats' => null,
        ]);
    }

    /**
     * Display user statistics page.
     */
    public function stats()
    {
        $user = Auth::user();
        $userStats = WordleUserStats::getOrCreateForUser($user->id);

        // Get today's word
        $todaysWord = WordleWord::where('publish_date', Carbon::today()->toDateString())->first();
        $todaysWordId = $todaysWord ? $todaysWord->id : null;

        // Check if user has completed today's puzzle
        $todayCompleted = false;
        if ($todaysWordId) {
            $todayProgress = $user->wordleProgress()
                ->where('word_id', $todaysWordId)
                ->whereNotNull('completed_at')
                ->first();

            $todayCompleted = (bool)$todayProgress;
        }

        // Build query for recent words
        $wordQuery = WordleWord::query();

        if ($todayCompleted) {
            $wordQuery->where('publish_date', '<=', Carbon::today()->toDateString());
        } else {
            $wordQuery->where('publish_date', '<', Carbon::today()->toDateString());
        }

        $recentWords = $wordQuery->orderBy('publish_date', 'desc')
            ->take(5)
            ->get();

        // Get progress for played words
        $wordIds = $recentWords->pluck('id')->toArray();
        $progress = $user->wordleProgress()
            ->whereIn('word_id', $wordIds)
            ->get()
            ->keyBy('word_id');

        // Filter to only include words that have been played
        $playedWords = $recentWords->filter(function ($word) use ($progress) {
            return isset($progress[$word->id]);
        });

        return view('ohiowordle.stats', [
            'userStats' => $userStats,
            'recentWords' => $playedWords,
            'wordProgress' => $progress,
            'seo' => $this->seoService->forOhioWordle(),
        ]);
    }
}
