<?php

namespace App\Modules\Geography\Queries;

use App\Models\City;
use App\Models\Content;
use App\Models\County;
use App\Models\Region;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class FetchLocationContent
{
    /**
     * Fetch all content for a location and its child locations.
     */
    public function execute(string $locatableType, int $locatableId, int $perPage = 12): LengthAwarePaginator
    {
        $query = Content::with(['contentCategories', 'contentType', 'author', 'locatable'])
            ->published()
            ->latest('published_at');

        $this->applyLocationFilter($query, $locatableType, $locatableId);

        return $query->paginate($perPage);
    }

    /**
     * Fetch content for a location filtered by category.
     */
    public function forCategory(
        string $locatableType,
        int $locatableId,
        int $categoryId,
        int $perPage = 12
    ): LengthAwarePaginator {
        $query = Content::with(['contentCategories', 'contentType', 'author', 'locatable'])
            ->whereHas('contentCategories', fn ($q) => $q->where('content_categories.id', $categoryId))
            ->published()
            ->latest('published_at');

        $this->applyLocationFilter($query, $locatableType, $locatableId);

        return $query->paginate($perPage);
    }

    /**
     * Apply location filter that includes the location and all child locations.
     */
    private function applyLocationFilter($query, string $locatableType, int $locatableId): void
    {
        if ($locatableType === Region::class) {
            // Region: include region content + county content + city content
            $query->where(function ($q) use ($locatableId) {
                // Direct region content
                $q->where(function ($sub) use ($locatableId) {
                    $sub->where('locatable_type', Region::class)
                        ->where('locatable_id', $locatableId);
                })
                // County content within region
                ->orWhere(function ($sub) use ($locatableId) {
                    $sub->where('locatable_type', County::class)
                        ->whereIn('locatable_id', function ($countyQuery) use ($locatableId) {
                            $countyQuery->select('id')
                                ->from('counties')
                                ->where('region_id', $locatableId);
                        });
                })
                // City content within region
                ->orWhere(function ($sub) use ($locatableId) {
                    $sub->where('locatable_type', City::class)
                        ->whereIn('locatable_id', function ($cityQuery) use ($locatableId) {
                            $cityQuery->select('cities.id')
                                ->from('cities')
                                ->join('counties', 'cities.county_id', '=', 'counties.id')
                                ->where('counties.region_id', $locatableId);
                        });
                });
            });
        } elseif ($locatableType === County::class) {
            // County: include county content + city content
            $query->where(function ($q) use ($locatableId) {
                // Direct county content
                $q->where(function ($sub) use ($locatableId) {
                    $sub->where('locatable_type', County::class)
                        ->where('locatable_id', $locatableId);
                })
                // City content within county
                ->orWhere(function ($sub) use ($locatableId) {
                    $sub->where('locatable_type', City::class)
                        ->whereIn('locatable_id', function ($cityQuery) use ($locatableId) {
                            $cityQuery->select('id')
                                ->from('cities')
                                ->where('county_id', $locatableId);
                        });
                });
            });
        } else {
            // City: only direct content
            $query->where('locatable_type', $locatableType)
                ->where('locatable_id', $locatableId);
        }
    }
}
