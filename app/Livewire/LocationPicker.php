<?php

namespace App\Livewire;

use App\Models\City;
use App\Models\County;
use App\Models\Region;
use Illuminate\Support\Collection;
use Livewire\Component;

class LocationPicker extends Component
{
    public ?int $regionId = null;
    public ?int $countyId = null;
    public ?int $cityId = null;

    public Collection $regions;
    public Collection $allCounties; // All counties for the main dropdown
    public Collection $cities;

    public function mount(?string $locatableType = null, ?int $locatableId = null): void
    {
        $this->regions = Region::orderBy('name')->get();
        $this->allCounties = County::with('region')->orderBy('name')->get();
        $this->cities = collect();

        // Initialize from existing values (when editing a draft)
        if ($locatableType && $locatableId) {
            $this->initializeFromLocation($locatableType, $locatableId);
        }
    }

    protected function initializeFromLocation(string $type, int $id): void
    {
        if ($type === City::class) {
            $city = City::with('county.region')->find($id);
            if ($city) {
                $this->regionId = $city->county->region_id;
                $this->countyId = $city->county_id;
                $this->cities = City::where('county_id', $this->countyId)->orderBy('name')->get();
                $this->cityId = $id;
            }
        } elseif ($type === County::class) {
            $county = County::with('region')->find($id);
            if ($county) {
                $this->regionId = $county->region_id;
                $this->countyId = $id;
                $this->cities = City::where('county_id', $this->countyId)->orderBy('name')->get();
            }
        } elseif ($type === Region::class) {
            $this->regionId = $id;
        }
    }

    public function updatedRegionId($value): void
    {
        // When region changes, clear county and city only if they don't belong to this region
        if ($value && $this->countyId) {
            $county = $this->allCounties->firstWhere('id', $this->countyId);
            if ($county && $county->region_id != $value) {
                $this->countyId = null;
                $this->cityId = null;
                $this->cities = collect();
            }
        } elseif (! $value) {
            // Region cleared - keep county if set, otherwise dispatch null
            if (! $this->countyId) {
                $this->dispatch('locationSelected', type: null, id: null);
                return;
            }
        }

        if ($value && ! $this->countyId) {
            $this->dispatch('locationSelected', type: Region::class, id: (int) $value);
        }
    }

    public function updatedCountyId($value): void
    {
        $this->cityId = null;
        $this->cities = collect();

        if ($value) {
            // Auto-set the region based on selected county
            $county = $this->allCounties->firstWhere('id', $value);
            if ($county) {
                $this->regionId = $county->region_id;
            }

            $this->cities = City::where('county_id', $value)->orderBy('name')->get();
            $this->dispatch('locationSelected', type: County::class, id: (int) $value);
        } elseif ($this->regionId) {
            $this->dispatch('locationSelected', type: Region::class, id: (int) $this->regionId);
        } else {
            $this->dispatch('locationSelected', type: null, id: null);
        }
    }

    public function updatedCityId($value): void
    {
        if ($value) {
            $this->dispatch('locationSelected', type: City::class, id: (int) $value);
        } elseif ($this->countyId) {
            $this->dispatch('locationSelected', type: County::class, id: (int) $this->countyId);
        }
    }

    public function getSelectedLocationProperty(): ?string
    {
        if ($this->cityId) {
            $city = $this->cities->firstWhere('id', $this->cityId);
            $county = $this->allCounties->firstWhere('id', $this->countyId);
            return $city?->name . ', ' . $county?->name . ' County';
        }

        if ($this->countyId) {
            $county = $this->allCounties->firstWhere('id', $this->countyId);
            return $county?->name . ' County';
        }

        if ($this->regionId) {
            $region = $this->regions->firstWhere('id', $this->regionId);
            return $region?->name;
        }

        return null;
    }

    public function render()
    {
        return view('livewire.location-picker');
    }
}
