<?php

namespace App\Livewire;

use App\Models\PollVote;
use App\Models\Thread;
use Livewire\Component;

class LatestPoll extends Component
{
    public $thread;
    public $poll;
    public $hasVoted = false;
    public $selectedOption = '';
    public $selectedOptions = [];

    public function mount()
    {
        $this->thread = Thread::whereHas('poll')
            ->with(['poll.pollOptions.votes', 'owner', 'forum'])
            ->orderBy('created_at', 'desc')
            ->first();

        if ($this->thread) {
            $this->poll = $this->thread->poll;
            $this->checkIfUserHasVoted();
        }
    }

    public function checkIfUserHasVoted()
    {
        if (!auth()->check() || !$this->poll) {
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
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if ($this->hasVoted || !$this->poll) {
            return;
        }

        $options = $this->poll->type === 'single'
            ? [$this->selectedOption]
            : $this->selectedOptions;

        foreach ($options as $optionId) {
            if (!empty($optionId)) {
                PollVote::create([
                    'user_id' => auth()->id(),
                    'poll_option_id' => $optionId
                ]);
            }
        }

        $this->hasVoted = true;
        $this->poll->refresh();
    }

    public function getVoteCountProperty()
    {
        if (!$this->poll) {
            return 0;
        }

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

    public function isVoteButtonDisabled()
    {
        if (!$this->poll) {
            return true;
        }

        if ($this->poll->type === 'single') {
            return empty($this->selectedOption);
        }
        return empty($this->selectedOptions);
    }

    public function render()
    {
        return view('livewire.latest-poll');
    }
}
