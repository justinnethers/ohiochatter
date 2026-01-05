<?php

namespace App\Livewire;

use App\Modules\Pickem\Models\Pickem;
use App\Modules\Pickem\Models\PickemPick;
use Livewire\Component;

class PickemAlertBar extends Component
{
    public ?Pickem $pickem = null;
    public bool $hasSubmitted = false;

    public function mount()
    {
        if (! auth()->check()) {
            return;
        }

        $this->pickem = Pickem::where(function ($query) {
            $query->whereNull('picks_lock_at')
                ->orWhere('picks_lock_at', '>', now());
        })
            ->orderBy('picks_lock_at', 'asc')
            ->first();

        if (! $this->pickem) {
            return;
        }

        // Don't show if already viewing this pickem
        if (request()->routeIs('pickem.show') && request()->route('pickem')?->id === $this->pickem->id) {
            $this->pickem = null;
            return;
        }

        $this->hasSubmitted = PickemPick::where('user_id', auth()->id())
            ->whereIn('pickem_matchup_id', $this->pickem->matchups->pluck('id'))
            ->exists();
    }

    public function render()
    {
        return view('livewire.pickem-alert-bar');
    }
}
