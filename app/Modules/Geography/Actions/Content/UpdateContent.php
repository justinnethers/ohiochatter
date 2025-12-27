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

            return $content->fresh(['contentCategory', 'contentType', 'author', 'locatable']);
        });
    }
}
