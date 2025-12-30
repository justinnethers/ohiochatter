<?php

namespace App\Modules\Geography\Queries;

use App\Models\City;
use App\Models\ContentCategory;
use App\Models\County;
use App\Models\Region;
use Illuminate\Database\Eloquent\Collection;

class FetchCategoriesForLocation
{
    /**
     * Fetch categories that have content in a location or its child locations.
     */
    public function execute(string $locatableType, int $locatableId): Collection
    {
        return ContentCategory::whereHas('content', function ($query) use ($locatableType, $locatableId) {
            $query->published();
            $this->applyLocationFilter($query, $locatableType, $locatableId);
        })
            ->active()
            ->ordered()
            ->get();
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
