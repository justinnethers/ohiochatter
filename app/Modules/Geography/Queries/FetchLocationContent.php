<?php

namespace App\Modules\Geography\Queries;

use App\Models\Content;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FetchLocationContent
{
    public function execute(string $locatableType, int $locatableId, int $perPage = 12): LengthAwarePaginator
    {
        return Content::where('locatable_type', $locatableType)
            ->where('locatable_id', $locatableId)
            ->with(['contentCategory', 'contentType', 'author'])
            ->published()
            ->latest('published_at')
            ->paginate($perPage);
    }

    public function forCategory(
        string $locatableType,
        int $locatableId,
        int $categoryId,
        int $perPage = 12
    ): LengthAwarePaginator {
        return Content::where('locatable_type', $locatableType)
            ->where('locatable_id', $locatableId)
            ->where('content_category_id', $categoryId)
            ->with(['contentType', 'author'])
            ->published()
            ->latest('published_at')
            ->paginate($perPage);
    }
}
