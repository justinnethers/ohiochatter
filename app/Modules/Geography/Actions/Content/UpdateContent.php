<?php

namespace App\Modules\Geography\Actions\Content;

use App\Models\Content;
use App\Modules\Geography\DTOs\UpdateContentData;
use Illuminate\Support\Facades\DB;

class UpdateContent
{
    public function execute(Content $content, UpdateContentData $data): Content
    {
        return DB::transaction(function () use ($content, $data) {
            $updateData = $data->toArray();

            if (!empty($updateData)) {
                $content->update($updateData);
            }

            // Sync categories if provided
            if ($data->categoryIds !== null) {
                $content->contentCategories()->sync($data->categoryIds);
            }

            return $content->fresh(['contentCategories', 'contentType', 'author', 'locatable']);
        });
    }
}
