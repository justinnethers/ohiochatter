<?php

namespace App\Livewire;

use App\Modules\Pickem\Models\Pickem;
use App\Modules\Pickem\Models\PickemComment;
use Livewire\Component;
use Livewire\WithPagination;

class PickemComments extends Component
{
    use WithPagination;

    public Pickem $pickem;
    public string $body = '';

    protected $rules = [
        'body' => 'required|min:3',
    ];

    public function mount(Pickem $pickem)
    {
        $this->pickem = $pickem;
    }

    public function addComment()
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $this->validate();

        PickemComment::create([
            'pickem_id' => $this->pickem->id,
            'user_id' => auth()->id(),
            'body' => $this->body,
        ]);

        $this->reset('body');
        $this->resetPage();
    }

    public function deleteComment($commentId)
    {
        $comment = PickemComment::find($commentId);

        if (! $comment) {
            return;
        }

        // Only allow owner or admin to delete
        if (auth()->id() !== $comment->user_id && ! auth()->user()?->is_admin) {
            return;
        }

        $comment->delete();
    }

    public function render()
    {
        return view('livewire.pickem-comments', [
            'comments' => $this->pickem->comments()
                ->with('owner')
                ->latest()
                ->paginate(20),
        ]);
    }
}
