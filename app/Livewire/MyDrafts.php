<?php

namespace App\Livewire;

use App\Models\GuideDraft;
use Illuminate\Support\Collection;
use Livewire\Component;

class MyDrafts extends Component
{
    public Collection $drafts;

    public function mount(): void
    {
        $this->loadDrafts();
    }

    protected function loadDrafts(): void
    {
        $this->drafts = GuideDraft::where('user_id', auth()->id())
            ->with(['contentCategory', 'contentType', 'locatable'])
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    public function deleteDraft(int $draftId): void
    {
        $draft = GuideDraft::where('id', $draftId)
            ->where('user_id', auth()->id())
            ->first();

        if ($draft) {
            $draft->delete();
            $this->loadDrafts();
        }
    }

    public function render()
    {
        return view('livewire.my-drafts');
    }
}
