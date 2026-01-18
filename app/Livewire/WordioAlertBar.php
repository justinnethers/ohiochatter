<?php

namespace App\Livewire;

use App\Modules\OhioWordle\Models\WordleWord;
use Livewire\Component;

class WordioAlertBar extends Component
{
    public ?WordleWord $word = null;
    public bool $hasPlayed = false;

    public function mount()
    {
        if (! auth()->check()) {
            return;
        }

        $this->word = WordleWord::getTodaysWord();

        if (! $this->word) {
            return;
        }

        // Don't show if already viewing Wordio
        if (request()->routeIs('ohiowordle.*')) {
            $this->word = null;
            return;
        }

        $this->hasPlayed = auth()->user()->wordleProgress()
            ->where('word_id', $this->word->id)
            ->whereNotNull('completed_at')
            ->exists();
    }

    public function render()
    {
        return view('livewire.wordio-alert-bar');
    }
}
