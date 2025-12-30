<?php

namespace App\Livewire;

use App\Models\Content;
use App\Models\GuideDraft;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Livewire\Component;

class MyGuides extends Component
{
    public Collection $drafts;
    public Collection $pendingGuides;
    public Collection $publishedGuides;

    #[Url(as: 'tab')]
    public string $activeTab = 'drafts';

    public function mount(): void
    {
        $this->loadContent();
    }

    protected function loadContent(): void
    {
        $userId = auth()->id();

        $this->drafts = GuideDraft::where('user_id', $userId)
            ->with(['contentCategory', 'locatable'])
            ->orderBy('updated_at', 'desc')
            ->get();

        $this->pendingGuides = Content::where('user_id', $userId)
            ->whereNull('published_at')
            ->with(['contentCategory', 'locatable'])
            ->orderBy('created_at', 'desc')
            ->get();

        $this->publishedGuides = Content::where('user_id', $userId)
            ->whereNotNull('published_at')
            ->with(['contentCategory', 'locatable'])
            ->orderBy('published_at', 'desc')
            ->get();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function deleteDraft(int $draftId): void
    {
        $draft = GuideDraft::where('id', $draftId)
            ->where('user_id', auth()->id())
            ->first();

        if ($draft) {
            $draft->delete();
            $this->loadContent();
        }
    }

    public function render()
    {
        return view('livewire.my-guides');
    }
}