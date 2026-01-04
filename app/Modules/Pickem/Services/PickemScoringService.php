<?php

namespace App\Modules\Pickem\Services;

use App\Models\User;
use App\Modules\Pickem\Models\Pickem;
use App\Modules\Pickem\Models\PickemGroup;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PickemScoringService
{
    public function getPickemLeaderboard(Pickem $pickem): Collection
    {
        $users = User::select('users.*')
            ->join('pickem_picks', 'users.id', '=', 'pickem_picks.user_id')
            ->join('pickem_matchups', 'pickem_picks.pickem_matchup_id', '=', 'pickem_matchups.id')
            ->where('pickem_matchups.pickem_id', $pickem->id)
            ->groupBy('users.id')
            ->get();

        return $users->map(function ($user) use ($pickem) {
            return [
                'user' => $user,
                'score' => $pickem->getUserScore($user),
                'max' => $pickem->getMaxPossibleScore(),
            ];
        })->sortByDesc('score')->values();
    }

    public function getGroupLeaderboard(PickemGroup $group): Collection
    {
        return $group->getLeaderboard();
    }

    public function calculateUserScore(Pickem $pickem, User $user): int
    {
        return $pickem->getUserScore($user);
    }
}
