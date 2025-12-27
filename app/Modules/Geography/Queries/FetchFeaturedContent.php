<?php

namespace App\Modules\Geography\Queries;

use App\Models\Content;
use Illuminate\Database\Eloquent\Collection;

class FetchFeaturedContent
{
    public function execute(string $locatableType, int $locatableId, int $limit = 6): Collection
    {
        return Content::where('locatable_type', $locatableType)
            ->where('locatable_id', $locatableId)
            ->with(['contentCategory', 'contentType', 'author'])
            ->featured()
            ->published()
            ->latest('published_at')
            ->take($limit)
            ->get();
    }
}
