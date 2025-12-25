<?php

namespace App\Livewire;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\Attributes\Computed;

class ActiveUsers extends Component
{
    #[Computed]
    public function activeUsers()
    {
        return Cache::remember('active_users', 300, function () {
            return User::query()
                ->where('last_activity', '>=', Carbon::now()->subMinutes(30))
                ->orderBy('last_activity', 'desc')
                ->get();
        });
    }

    public function render()
    {
        return view('livewire.active-users');
    }
}
