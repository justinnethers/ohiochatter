<?php

namespace App\Livewire;

use App\Models\Content;
use App\Models\ContentRevision;
use App\Modules\Geography\Actions\Content\ApproveContentRevision;
use App\Modules\Geography\Actions\Content\RejectContentRevision;
use Livewire\Attributes\Locked;
use Livewire\Component;

class ReviewRevision extends Component
{
    #[Locked]
    public int $revisionId;

    #[Locked]
    public int $contentId;

    public bool $showPreview = false;
    public string $rejectReason = '';
    public bool $showRejectModal = false;
    public bool $processed = false;
    public string $processedAction = '';

    public function mount(ContentRevision $revision): void
    {
        $this->revisionId = $revision->id;
        $this->contentId = $revision->content_id;
    }

    public function togglePreview(): void
    {
        $this->showPreview = !$this->showPreview;
    }

    public function approve(): void
    {
        $revision = ContentRevision::findOrFail($this->revisionId);

        app(ApproveContentRevision::class)->execute($revision, auth()->id());

        $this->processed = true;
        $this->processedAction = 'approved';
    }

    public function openRejectModal(): void
    {
        $this->showRejectModal = true;
    }

    public function closeRejectModal(): void
    {
        $this->showRejectModal = false;
        $this->rejectReason = '';
    }

    public function reject(): void
    {
        $revision = ContentRevision::findOrFail($this->revisionId);

        app(RejectContentRevision::class)->execute(
            $revision,
            auth()->id(),
            $this->rejectReason ?: null
        );

        $this->processed = true;
        $this->processedAction = 'rejected';
        $this->showRejectModal = false;
    }

    public function render()
    {
        $revision = ContentRevision::with('author')->findOrFail($this->revisionId);
        $content = Content::findOrFail($this->contentId);

        return view('livewire.review-revision', [
            'revision' => $revision,
            'content' => $content,
        ]);
    }
}
