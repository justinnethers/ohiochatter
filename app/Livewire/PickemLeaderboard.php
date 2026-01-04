<?php

namespace App\Livewire;

use App\Models\User;
use App\Modules\Pickem\Models\Pickem;
use App\Modules\Pickem\Models\PickemPick;
use Illuminate\Support\Collection;
use Livewire\Component;

class PickemLeaderboard extends Component
{
    public Pickem $pickem;

    public function mount(Pickem $pickem)
    {
        $this->pickem = $pickem;
    }

    public function getLeaderboardProperty(): Collection
    {
        // Get all users who made picks on this pickem
        $userIds = PickemPick::whereIn('pickem_matchup_id', $this->pickem->matchups->pluck('id'))
            ->distinct()
            ->pluck('user_id');

        $users = User::whereIn('id', $userIds)->get();

        return $users->map(function ($user) {
            return [
                'user' => $user,
                'score' => $this->pickem->getUserScore($user),
                'max' => $this->pickem->getMaxPossibleScore(),
            ];
        })->sortByDesc('score')->values();
    }

    public function render()
    {
        return view('livewire.pickem-leaderboard', [
            'leaderboard' => $this->leaderboard,
        ]);
    }
}
