<?php

namespace App\Livewire;

use App\Models\Poll;
use App\Models\Thread;
use Livewire\Component;
use App\Models\Reply;

class PostComponent extends Component
{
    public Reply | Thread $post;
    public Poll | bool $poll;
    public $editMode = false;
    public $body;

    protected $rules = [
        'body' => 'required',
    ];

    public function mount($post): void
    {
        $this->post = $post;
        $this->body = $post->body;
    }

    public function toggleEditMode(): void
    {
        $this->editMode = !$this->editMode;
        $this->dispatch('toggle-editor', [
            'editorId' => 'editor-' . $this->post->id,
            'content' => $this->body,
            'isEdit' => $this->editMode
        ]);
    }

    public function save(): void
    {
        $this->validate();
        $this->post->body = $this->body;
        $this->post->save();
        $this->editMode = false;
        $this->dispatch('editor-saved');
        session()->flash('message', 'Post updated successfully.');
    }

    public function render()
    {
        return view('livewire.post-component');
    }
}
