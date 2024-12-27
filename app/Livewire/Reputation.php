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
        if (!$this->authCheck() || $this->userOwnsPost()) {
            return;
        }

        $this->post->rep();
        $this->post->refresh();
    }

    public function neg()
    {
        if (!$this->authCheck() || $this->userOwnsPost()) {
            return;
        }

        $this->post->neg();
        $this->post->refresh();
    }

    private function authCheck()
    {
        if (! auth()->check()) {
            $this->redirect(route('login'));
            return false;
        }
        return true;
    }

    private function userOwnsPost()
    {
        return auth()->id() === $this->post->user_id;
    }
}
