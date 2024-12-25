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
        try {
            $this->post->body = $this->body;
            \Log::info('Saving post:', [
                'post_id' => $this->post->id,
                'body' => $this->body,
                'post_type' => get_class($this->post)
            ]);

            $result = $this->post->save();
            \Log::info('Save result:', ['success' => $result]);

            $this->editMode = false;
            $this->dispatch('editor-saved');
            session()->flash('message', 'Post updated successfully.');
        } catch (\Exception $e) {
            \Log::error('Error saving post:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Failed to save post: ' . $e->getMessage());
        }
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
