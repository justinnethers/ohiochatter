<?php

namespace App\Modules\Geography\Queries;

use App\Models\City;
use App\Models\County;
use App\Models\Region;
use App\Modules\Geography\ValueObjects\LocationPath;

class FetchLocationHierarchy
{
    public function execute(?Region $region, ?County $county = null, ?City $city = null): LocationPath
    {
        $this->validateHierarchy($region, $county, $city);

        return new LocationPath(
            region: $region,
            county: $county,
            city: $city
        );
    }

    public function validateHierarchy(?Region $region, ?County $county = null, ?City $city = null): void
    {
        if ($county && $region && $county->region_id !== $region->id) {
            abort(404, 'County does not belong to this region');
        }

        if ($city && (!$county || $city->county_id !== $county->id)) {
            abort(404, 'City does not belong to this county');
        }
    }
}
