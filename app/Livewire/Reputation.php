<?php

namespace App\Livewire;

use App\Models\Reply;
use Livewire\Component;

class Reputation extends Component
{
    public Reply $post;

    public function render()
    {
        return view('livewire.reputation');
    }

    public function rep()
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $this->post->rep();
        $this->post->refresh();
    }

    public function neg()
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $this->post->neg();
        $this->post->refresh();
    }
}
