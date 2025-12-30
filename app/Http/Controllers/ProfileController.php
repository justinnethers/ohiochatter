<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Content;
use App\Models\Rep;
use App\Models\Neg;
use App\Models\Thread;
use App\Models\User;
use App\Services\ReplyPaginationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display a user's public profile.
     */
    public function show(User $user): View
    {
        // Track profile views (throttled to once per 10 minutes per viewer)
        if (auth()->id() !== $user->id) {
            $viewerId = auth()->id() ?? request()->ip();
            $cacheKey = "profile_view:{$viewerId}:{$user->id}";

            if (!Cache::has($cacheKey)) {
                $user->increment('profile_views');
                Cache::put($cacheKey, true, now()->addMinutes(10));
            }
        }

        // Get user's recent posts (replies) with position for pagination
        $recentPosts = $user->replies()
            ->with(['thread', 'thread.forum'])
            ->select('replies.*')
            ->selectSub(ReplyPaginationService::positionSubquery(), 'position')
            ->latest()
            ->take(10)
            ->get();

        // Get user's threads
        $threads = Thread::where('user_id', $user->id)
            ->with('forum')
            ->latest()
            ->take(5)
            ->get();

        // Count total reps received on user's posts (cached for 5 minutes)
        $totalReps = Cache::remember("user:{$user->id}:total_reps", 300, function () use ($user) {
            $replyReps = Rep::where('repped_type', 'App\Models\Reply')
                ->whereIn('repped_id', $user->replies()->select('id'))
                ->count();
            $threadReps = Rep::where('repped_type', 'App\Models\Thread')
                ->whereIn('repped_id', Thread::where('user_id', $user->id)->select('id'))
                ->count();
            return $replyReps + $threadReps;
        });

        // Count total negs received on user's posts (cached for 5 minutes)
        $totalNegs = Cache::remember("user:{$user->id}:total_negs", 300, function () use ($user) {
            $replyNegs = Neg::where('negged_type', 'App\Models\Reply')
                ->whereIn('negged_id', $user->replies()->select('id'))
                ->count();
            $threadNegs = Neg::where('negged_type', 'App\Models\Thread')
                ->whereIn('negged_id', Thread::where('user_id', $user->id)->select('id'))
                ->count();
            return $replyNegs + $threadNegs;
        });

        // Get game stats if they exist
        $gameStats = $user->gameStats;

        // Get user's published guides
        $guides = Content::where('user_id', $user->id)
            ->whereNotNull('published_at')
            ->with(['contentCategory', 'locatable'])
            ->latest('published_at')
            ->take(5)
            ->get();

        // Get actual last post date from replies
        $lastPostDate = $recentPosts->first()?->created_at;

        // Calculate account age
        $joinDate = $user->legacy_join_date
            ? \Carbon\Carbon::parse($user->legacy_join_date)
            : $user->created_at;
        $accountAge = $joinDate->diffForHumans(null, true);

        return view('profile.show', compact(
            'user',
            'recentPosts',
            'threads',
            'guides',
            'totalReps',
            'totalNegs',
            'gameStats',
            'lastPostDate',
            'joinDate',
            'accountAge'
        ));
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
