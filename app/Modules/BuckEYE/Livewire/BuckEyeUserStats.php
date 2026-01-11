<?php

namespace App\Modules\BuckEYE\Livewire;

use App\Modules\BuckEYE\Models\UserGameStats;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class BuckEyeUserStats extends Component
{
    /**
     * User game stats
     */
    public $userStats;

    /**
     * Listen for Livewire events
     */
    protected $listeners = ['gameCompleted' => 'refreshStats'];

    /**
     * Initialize the component
     */
    public function mount()
    {
        $this->loadStats();
    }

    /**
     * Load user stats from database
     */
    public function loadStats()
    {
        if (Auth::check()) {
            $this->userStats = UserGameStats::getOrCreateForUser(Auth::id());
        }
    }

    /**
     * Refresh stats when a game is completed
     */
    public function refreshStats()
    {
        $this->loadStats();
    }

    public function render()
    {
        return view('livewire.buck-eye-user-stats');
    }
}
