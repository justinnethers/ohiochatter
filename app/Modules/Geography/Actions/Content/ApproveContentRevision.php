<?php

namespace App\Modules\Geography\Actions\Content;

use App\Models\ContentRevision;
use App\Notifications\RevisionApproved;
use Illuminate\Support\Facades\DB;

class ApproveContentRevision
{
    public function execute(ContentRevision $revision, int $reviewerId): ContentRevision
    {
        $revision = DB::transaction(function () use ($revision, $reviewerId) {
            $content = $revision->content;

            // Build update data from revision (only non-null values)
            $updateData = array_filter([
                'title' => $revision->title,
                'excerpt' => $revision->excerpt,
                'body' => $revision->body,
                'blocks' => $revision->blocks,
                'metadata' => $revision->metadata,
                'featured_image' => $revision->featured_image,
                'gallery' => $revision->gallery,
            ], fn ($value) => $value !== null);

            // Apply revision to content
            if (!empty($updateData)) {
                $content->update($updateData);
            }

            // Sync categories if provided
            if ($revision->category_ids !== null) {
                $content->contentCategories()->sync($revision->category_ids);
            }

            // Mark revision as approved
            $revision->update([
                'status' => 'approved',
                'reviewed_by' => $reviewerId,
                'reviewed_at' => now(),
            ]);

            return $revision->fresh();
        });

        // Notify author
        $revision->author->notify(new RevisionApproved($revision));

        return $revision;
    }
}
