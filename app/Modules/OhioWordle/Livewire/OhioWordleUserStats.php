<?php

namespace App\Modules\OhioWordle\Livewire;

use App\Modules\OhioWordle\Models\WordleUserStats;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class OhioWordleUserStats extends Component
{
    public $userStats;

    public function mount()
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        if (Auth::check()) {
            $this->userStats = WordleUserStats::getOrCreateForUser(Auth::id());
        }
    }

    #[On('gameCompleted')]
    public function refreshStats()
    {
        $this->loadStats();
    }

    public function render()
    {
        return view('livewire.ohio-wordle-user-stats');
    }
}
