<?php

namespace App\Modules\Geography\ValueObjects;

use App\Models\City;
use App\Models\County;
use App\Models\Region;

readonly class LocationPath
{
    public function __construct(
        public ?Region $region = null,
        public ?County $county = null,
        public ?City $city = null,
    ) {}

    public function getMostSpecificLocation(): Region|County|City|null
    {
        return $this->city ?? $this->county ?? $this->region;
    }

    public function getLocatableType(): ?string
    {
        return match (true) {
            $this->city !== null => City::class,
            $this->county !== null => County::class,
            $this->region !== null => Region::class,
            default => null,
        };
    }

    public function getLocatableId(): ?int
    {
        return $this->getMostSpecificLocation()?->id;
    }

    public function getLocationName(): ?string
    {
        if ($this->city) {
            return $this->city->name;
        }

        if ($this->county) {
            return $this->county->name;
        }

        if ($this->region) {
            return $this->region->name;
        }

        return null;
    }

    public function toBreadcrumbs(): array
    {
        $crumbs = [];

        if ($this->region) {
            $crumbs['region'] = [
                'name' => $this->region->name,
                'url' => route('region.show', $this->region),
            ];
        }

        if ($this->county) {
            $crumbs['county'] = [
                'name' => $this->county->name,
                'url' => route('county.show', [$this->region, $this->county]),
            ];
        }

        if ($this->city) {
            $crumbs['city'] = [
                'name' => $this->city->name,
                'url' => route('city.show', [$this->region, $this->county, $this->city]),
            ];
        }

        return $crumbs;
    }

    public function isEmpty(): bool
    {
        return $this->region === null && $this->county === null && $this->city === null;
    }

    public function hasCity(): bool
    {
        return $this->city !== null;
    }

    public function hasCounty(): bool
    {
        return $this->county !== null;
    }

    public function hasRegion(): bool
    {
        return $this->region !== null;
    }
}
