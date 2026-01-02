<?php

namespace App\Modules\Geography\Actions\Content;

use App\Models\ContentRevision;
use App\Notifications\RevisionRejected;
use Illuminate\Support\Facades\DB;

class RejectContentRevision
{
    public function execute(ContentRevision $revision, int $reviewerId, ?string $notes = null): ContentRevision
    {
        $revision = DB::transaction(function () use ($revision, $reviewerId, $notes) {
            $revision->update([
                'status' => 'rejected',
                'reviewed_by' => $reviewerId,
                'reviewed_at' => now(),
                'review_notes' => $notes,
            ]);

            return $revision->fresh();
        });

        // Notify author
        $revision->author->notify(new RevisionRejected($revision));

        return $revision;
    }
}
