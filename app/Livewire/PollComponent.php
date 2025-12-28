<?php

namespace App\Livewire;

use App\Models\Poll;
use App\Models\PollVote;
use Livewire\Component;

class PollComponent extends Component
{
    public $poll;
    public $hasVoted = false;
    public $selectedOption = '';
    public $selectedOptions = [];

    public function mount(Poll $poll)
    {
        $this->poll = $poll;
        $this->checkIfUserHasVoted();
    }

    public function checkIfUserHasVoted()
    {
        if (!auth()->check()) {
            return;
        }

        foreach ($this->poll->pollOptions as $option) {
            foreach ($option->votes as $vote) {
                if ($vote->user_id === auth()->id()) {
                    $this->hasVoted = true;
                    return;
                }
            }
        }
    }

    public function vote()
    {
        \Log::info('Vote method called');
        \Log::info('Selected option:', ['option' => $this->selectedOption]);
        \Log::info('Selected options:', ['options' => $this->selectedOptions]);

        if (!auth()->check()) {
            \Log::info('User not authenticated');
            return redirect()->route('login');
        }

        if ($this->hasVoted) {
            \Log::info('User has already voted');
            return;
        }

        $options = $this->poll->type === 'single'
            ? [$this->selectedOption]
            : $this->selectedOptions;

        \Log::info('Processing options:', ['options' => $options]);

        foreach ($options as $optionId) {
            if (!empty($optionId)) {
                \Log::info('Creating vote for option:', ['optionId' => $optionId]);
                PollVote::create([
                    'user_id' => auth()->id(),
                    'poll_option_id' => $optionId
                ]);
            }
        }

        $this->hasVoted = true;
        $this->poll->refresh();
    }

    public function test()
    {
        \Log::info('Test method called');
        dd('Test method called'); // This will cause the page to stop and show debug info
    }

    public function getVoteCountProperty()
    {
        $count = 0;
        foreach ($this->poll->pollOptions as $option) {
            $count += $option->votes->count();
        }
        return $count;
    }

    public function getPercentage($option)
    {
        if ($this->voteCount === 0) {
            return 0;
        }

        return round(($option->votes->count() / $this->voteCount) * 100);
    }

    public function getRankedOptionsProperty()
    {
        return $this->poll->pollOptions->sortByDesc(fn($option) => $option->votes->count())->values();
    }

    public function getOptionRank($option)
    {
        $ranked = $this->rankedOptions;
        foreach ($ranked as $index => $rankedOption) {
            if ($rankedOption->id === $option->id) {
                return $index + 1;
            }
        }
        return count($ranked);
    }

    public function updatedSelectedOption($value)
    {
        \Log::info('Selected option updated:', ['value' => $value]);
    }

    public function isVoteButtonDisabled()
    {
        if ($this->poll->type === 'single') {
            return empty($this->selectedOption);
        }
        return empty($this->selectedOptions);
    }

    public function render()
    {
        return view('livewire.poll-component');
    }
}
