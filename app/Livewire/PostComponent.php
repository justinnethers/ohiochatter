<?php

namespace App\Livewire;

use App\Models\Poll;
use App\Models\Reply;
use App\Models\Thread;
use App\Services\MentionService;
use Livewire\Attributes\On;
use Livewire\Component;

class PostComponent extends Component
{
    public Reply|Thread $post;

    public Poll|bool $poll = false;

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
        $this->editMode = ! $this->editMode;
        if (! $this->editMode) {
            $this->dispatch('destroy-editor', ['editorId' => $this->post->id]);
        }
    }

    public function save(): void
    {
        $this->post->body = $this->body;
        $this->post->save();

        // Process any new mentions in the edited body
        app(MentionService::class)->processMentions($this->body, $this->post, auth()->user());

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
