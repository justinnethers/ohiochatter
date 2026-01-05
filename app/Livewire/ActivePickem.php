<?php

namespace App\Livewire;

use App\Modules\Pickem\Models\Pickem;
use App\Modules\Pickem\Models\PickemPick;
use Livewire\Component;

class ActivePickem extends Component
{
    public ?Pickem $pickem = null;
    public array $picks = [];
    public bool $hasSubmitted = false;

    public function mount()
    {
        $this->pickem = Pickem::with(['matchups', 'group'])
            ->where(function ($query) {
                $query->whereNull('picks_lock_at')
                    ->orWhere('picks_lock_at', '>', now());
            })
            ->orderBy('picks_lock_at', 'asc')
            ->first();

        if ($this->pickem) {
            $this->loadExistingPicks();
        }
    }

    public function loadExistingPicks()
    {
        if (! auth()->check() || ! $this->pickem) {
            return;
        }

        $existingPicks = PickemPick::where('user_id', auth()->id())
            ->whereIn('pickem_matchup_id', $this->pickem->matchups->pluck('id'))
            ->get();

        foreach ($existingPicks as $pick) {
            $this->picks[$pick->pickem_matchup_id] = $pick->pick;
        }

        $this->hasSubmitted = $existingPicks->isNotEmpty();
    }

    public function makePick($matchupId, $option)
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        if (! $this->pickem || $this->pickem->isLocked()) {
            return;
        }

        $this->picks[$matchupId] = $option;
    }

    public function submitPicks()
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        if (! $this->pickem || $this->pickem->isLocked()) {
            return;
        }

        // Validate all matchups have picks
        $matchupIds = $this->pickem->matchups->pluck('id')->toArray();
        foreach ($matchupIds as $matchupId) {
            if (! isset($this->picks[$matchupId])) {
                return;
            }
        }

        foreach ($this->picks as $matchupId => $pick) {
            PickemPick::updateOrCreate(
                ['user_id' => auth()->id(), 'pickem_matchup_id' => $matchupId],
                ['pick' => $pick]
            );
        }

        $this->hasSubmitted = true;
        $this->pickem->refresh();
    }

    public function getParticipantCountProperty(): int
    {
        return $this->pickem?->getParticipantCount() ?? 0;
    }

    public function render()
    {
        return view('livewire.active-pickem');
    }
}
