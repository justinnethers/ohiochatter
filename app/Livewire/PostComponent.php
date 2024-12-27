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
    public $canEdit = false;
    public $body;

    protected $rules = [
        'body' => 'required',
    ];

    public function mount($post): void
    {
        $this->post = $post;
        $this->body = $post->body;
        $this->canEdit = $this->post->owner->id === \Auth::id() || \Auth::user()->is_admin;
    }

    public function toggleEditMode(): void
    {
        \Log::info('Toggle edit mode', ['current' => $this->editMode]);
        $this->editMode = !$this->editMode;
        if (!$this->editMode) {
            \Log::info('Dispatching destroy-editor');
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

    public function updated($field)
    {
        \Log::info('Field updated:', ['field' => $field, 'value' => $this->$field]);
    }

    public function render()
    {
        \Log::info('Rendering post component:', [
            'post_id' => $this->post->id,
            'edit_mode' => $this->editMode,
            'body_length' => strlen($this->body)
        ]);
        return view('livewire.post-component');
    }
}
