<?php

namespace App\Modules\Geography\Actions\Content;

use App\Models\ContentRevision;
use App\Models\User;
use App\Modules\Geography\DTOs\CreateRevisionData;
use App\Notifications\RevisionSubmittedForReview;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class CreateContentRevision
{
    public function execute(CreateRevisionData $data, int $userId): ContentRevision
    {
        $revision = DB::transaction(function () use ($data, $userId) {
            // Cancel any existing pending revisions for this content by this user
            ContentRevision::where('content_id', $data->contentId)
                ->where('user_id', $userId)
                ->where('status', 'pending')
                ->update([
                    'status' => 'rejected',
                    'review_notes' => 'Superseded by newer revision',
                ]);

            $revision = ContentRevision::create([
                'content_id' => $data->contentId,
                'user_id' => $userId,
                'title' => $data->title,
                'excerpt' => $data->excerpt,
                'body' => $data->body,
                'blocks' => $data->blocks,
                'metadata' => $data->metadata,
                'featured_image' => $data->featuredImage,
                'gallery' => $data->gallery,
                'category_ids' => $data->categoryIds,
                'status' => 'pending',
            ]);

            return $revision->fresh(['content', 'author']);
        });

        // Notify admins
        $admins = User::where('is_admin', true)->get();
        if ($admins->isNotEmpty()) {
            Notification::send($admins, new RevisionSubmittedForReview($revision));
        }

        return $revision;
    }
}
