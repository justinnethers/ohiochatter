<?php

namespace App\Modules\Geography\Queries;

use App\Models\ContentCategory;
use Illuminate\Database\Eloquent\Collection;

class FetchCategoriesForLocation
{
    public function execute(string $locatableType, int $locatableId): Collection
    {
        return ContentCategory::whereHas('content', function ($query) use ($locatableType, $locatableId) {
            $query->where('locatable_type', $locatableType)
                ->where('locatable_id', $locatableId)
                ->published();
        })
            ->active()
            ->ordered()
            ->get();
    }
}
