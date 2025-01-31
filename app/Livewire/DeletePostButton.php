<?php

namespace App\Livewire;

use App\Models\Reply;
use App\Models\Thread;
use Livewire\Component;

class DeletePostButton extends Component
{
    public Reply | Thread $post;

    public function render()
    {
        return view('livewire.delete-post-button');
    }

    public function delete()
    {
        $this->post->delete();
        $this->dispatch('removed-post-' . $this->post->id);
    }
}
