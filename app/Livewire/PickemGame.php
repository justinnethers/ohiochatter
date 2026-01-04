<?php

namespace App\Livewire;

use App\Modules\Pickem\Models\Pickem;
use App\Modules\Pickem\Models\PickemPick;
use Livewire\Component;

class PickemGame extends Component
{
    public Pickem $pickem;
    public array $picks = [];
    public array $confidences = [];
    public bool $hasSubmitted = false;

    public function mount(Pickem $pickem)
    {
        $this->pickem = $pickem;
        $this->loadExistingPicks();
    }

    public function loadExistingPicks()
    {
        if (! auth()->check()) {
            return;
        }

        $existingPicks = PickemPick::where('user_id', auth()->id())
            ->whereIn('pickem_matchup_id', $this->pickem->matchups->pluck('id'))
            ->get();

        foreach ($existingPicks as $pick) {
            $this->picks[$pick->pickem_matchup_id] = $pick->pick;
            $this->confidences[$pick->pickem_matchup_id] = $pick->confidence;
        }

        $this->hasSubmitted = $existingPicks->isNotEmpty();
    }

    public function makePick($matchupId, $option)
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        if ($this->pickem->isLocked()) {
            return;
        }

        $this->picks[$matchupId] = $option;
    }

    public function setConfidence($matchupId, $value)
    {
        if ($this->pickem->scoring_type !== 'confidence') {
            return;
        }

        // Remove confidence from any other matchup that has this value
        foreach ($this->confidences as $id => $conf) {
            if ($conf == $value && $id != $matchupId) {
                unset($this->confidences[$id]);
            }
        }

        $this->confidences[$matchupId] = (int) $value;
    }

    public function submitPicks()
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        if ($this->pickem->isLocked()) {
            session()->flash('error', 'This Pick \'Em is locked.');

            return;
        }

        // Validate all matchups have picks
        $matchupIds = $this->pickem->matchups->pluck('id')->toArray();
        foreach ($matchupIds as $matchupId) {
            if (! isset($this->picks[$matchupId])) {
                session()->flash('error', 'Please make a pick for all matchups.');

                return;
            }
        }

        // For confidence mode, validate all confidence values are assigned
        if ($this->pickem->scoring_type === 'confidence') {
            $expectedConfidences = range(1, count($matchupIds));
            $assignedConfidences = array_values(array_filter($this->confidences));
            sort($assignedConfidences);

            if ($assignedConfidences !== $expectedConfidences) {
                session()->flash('error', 'Please assign a unique confidence value (1-'.count($matchupIds).') to each matchup.');

                return;
            }
        }

        foreach ($this->picks as $matchupId => $pick) {
            PickemPick::updateOrCreate(
                ['user_id' => auth()->id(), 'pickem_matchup_id' => $matchupId],
                [
                    'pick' => $pick,
                    'confidence' => $this->confidences[$matchupId] ?? null,
                ]
            );
        }

        $this->hasSubmitted = true;
        $this->pickem->refresh();

        session()->flash('success', 'Your picks have been saved!');
    }

    public function getUsedConfidencesProperty(): array
    {
        return array_values(array_filter($this->confidences));
    }

    public function render()
    {
        return view('livewire.pickem-game');
    }
}
