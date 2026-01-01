<?php

namespace App\Modules\Geography\Actions\Content;

use App\Models\Content;
use App\Modules\Geography\DTOs\CreateContentData;
use App\Modules\Geography\Events\ContentCreated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateContent
{
    public function execute(CreateContentData $data, int $userId): Content
    {
        return DB::transaction(function () use ($data, $userId) {
            $content = Content::create([
                'content_type_id' => $data->contentTypeId,
                'user_id' => $userId,
                'locatable_type' => $data->locatableType,
                'locatable_id' => $data->locatableId,
                'title' => $data->title,
                'slug' => $data->slug ?? Str::slug($data->title),
                'excerpt' => $data->excerpt,
                'body' => $data->body ?? '',
                'metadata' => $data->metadata,
                'featured_image' => $data->featuredImage,
                'gallery' => $data->gallery,
                'meta_title' => $data->metaTitle ?? $data->title,
                'meta_description' => $data->metaDescription ?? $data->excerpt ?? '',
                'featured' => $data->featured,
                'published_at' => $data->publishedAt,
                'blocks' => $data->blocks,
            ]);

            // Sync categories via pivot table
            if (!empty($data->categoryIds)) {
                $content->contentCategories()->sync($data->categoryIds);
            }

            event(new ContentCreated($content));

            return $content->fresh(['contentCategories', 'contentType', 'author', 'locatable']);
        });
    }
}
