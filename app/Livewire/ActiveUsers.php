<?php

namespace App\Livewire;

use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\Attributes\Computed;

class ActiveUsers extends Component
{
    #[Computed]
    public function activeUsers()
    {
        return User::query()
            ->where('last_activity', '>=', Carbon::now()->subMinutes(30))
            ->orderBy('last_activity', 'desc')
            ->get();
    }

    public function render()
    {
        return view('livewire.active-users');
    }
}
