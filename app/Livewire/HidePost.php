<?php

namespace App\Livewire;

use App\Models\Reply;
use Livewire\Component;

class HidePost extends Component
{
    public Reply $post;

    public function hide()
    {
        $this->post->update([
            'hidden' => 1
        ]);
    }

    public function render()
    {
        return view('livewire.hide-post');
    }
}
