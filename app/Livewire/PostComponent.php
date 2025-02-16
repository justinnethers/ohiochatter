<?php

namespace App\Livewire;

use App\Models\Poll;
use App\Models\Thread;
use Livewire\Attributes\On;
use Livewire\Component;
use App\Models\Reply;

class PostComponent extends Component
{
    public Reply | Thread $post;
    public Poll | bool $poll;
    public $editMode = false;
    public $canEdit = false;
    public $body;
    public bool $firstPostOnPage;

    protected $rules = [
        'body' => 'required',
    ];

    public function mount($post): void
    {
        $this->post = $post;
        $this->body = $post->body;
        $this->canEdit = $this->post->owner->id === \Auth::id() || \Auth::user() && \Auth::user()->is_admin;
    }

    public function toggleEditMode(): void
    {
        $this->editMode = !$this->editMode;
        if (!$this->editMode) {
            $this->dispatch('destroy-editor', ['editorId' => $this->post->id]);
        }
    }

    public function save(): void
    {
        $this->post->body = $this->body;
        $this->post->save();
        $this->editMode = false;
        $this->dispatch('destroy-editor', ['editorId' => $this->post->id]);
        session()->flash('message', 'Post updated successfully.');
    }

    #[On('post-deleted')]
    public function handlePostDeleted()
    {
        $this->dispatch('remove-post', postId: $this->post->id);
    }

    public function render()
    {
        return view('livewire.post-component');
    }
}
