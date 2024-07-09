<?php

namespace App\Livewire;

use App\Models\Reply;
use App\Models\Thread;
use Livewire\Component;

class Reputation extends Component
{
    public Reply | Thread $post;

    public function render()
    {
        return view('livewire.reputation');
    }

    public function rep()
    {
        $this->authCheck();

        if ($this->userOwnsPost()) {
            return;
        }

        $this->post->rep();
        $this->post->refresh();
    }

    public function neg()
    {
        $this->authCheck();

        if ($this->userOwnsPost()) {
            return;
        }

        $this->post->neg();
        $this->post->refresh();
    }

    private function authCheck()
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }
    }

    private function userOwnsPost()
    {
        return auth()->id() === $this->post->user_id;
    }
}
