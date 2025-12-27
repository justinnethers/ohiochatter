<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Rep;
use App\Models\Neg;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display a user's public profile.
     */
    public function show(User $user): View
    {
        // Get user's recent posts (replies)
        $recentPosts = $user->replies()
            ->with(['thread', 'thread.forum'])
            ->latest()
            ->take(10)
            ->get();

        // Get user's threads
        $threads = Thread::where('user_id', $user->id)
            ->with('forum')
            ->latest()
            ->take(5)
            ->get();

        // Count total reps received on user's posts
        $totalReps = Rep::whereHasMorph('repped', ['App\Models\Reply', 'App\Models\Thread'], function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->count();

        // Count total negs received on user's posts
        $totalNegs = Neg::whereHasMorph('negged', ['App\Models\Reply', 'App\Models\Thread'], function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->count();

        // Get game stats if they exist
        $gameStats = $user->gameStats;

        // Calculate account age
        $joinDate = $user->legacy_join_date
            ? \Carbon\Carbon::parse($user->legacy_join_date)
            : $user->created_at;
        $accountAge = $joinDate->diffForHumans(null, true);

        return view('profile.show', compact(
            'user',
            'recentPosts',
            'threads',
            'totalReps',
            'totalNegs',
            'gameStats',
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
