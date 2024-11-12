<?php

namespace App\Livewire;

use App\Models\Reply;
use App\Models\Thread;
use Livewire\Component;

class EditPost extends Component
{
    public Thread | Reply $post;


    public function editPost()
    {
        $this->authCheck();
        if (!$this->userOwnsPost()) {
                abort(403);
        }
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
