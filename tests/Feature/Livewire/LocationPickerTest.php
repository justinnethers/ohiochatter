<?php

use App\Livewire\LocationPicker;
use App\Models\City;
use App\Models\County;
use App\Models\Region;
use Livewire\Livewire;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->region = Region::factory()->create(['name' => 'Central Ohio']);
    $this->county = County::factory()->for($this->region)->create(['name' => 'Franklin']);
    $this->city = City::factory()->for($this->county)->create(['name' => 'Columbus']);
});

it('renders successfully', function () {
    Livewire::test(LocationPicker::class)
        ->assertStatus(200)
        ->assertViewIs('livewire.location-picker');
});

it('loads regions on mount', function () {
    Livewire::test(LocationPicker::class)
        ->assertSee('Central Ohio');
});

it('loads counties when region is selected', function () {
    Livewire::test(LocationPicker::class)
        ->set('regionId', $this->region->id)
        ->assertSet('counties', fn ($counties) => $counties->contains('id', $this->county->id));
});

it('loads cities when county is selected', function () {
    Livewire::test(LocationPicker::class)
        ->set('regionId', $this->region->id)
        ->set('countyId', $this->county->id)
        ->assertSet('cities', fn ($cities) => $cities->contains('id', $this->city->id));
});

it('dispatches event when region is selected', function () {
    Livewire::test(LocationPicker::class)
        ->set('regionId', $this->region->id)
        ->assertDispatched('locationSelected', type: Region::class, id: $this->region->id);
});

it('dispatches event when county is selected', function () {
    Livewire::test(LocationPicker::class)
        ->set('regionId', $this->region->id)
        ->set('countyId', $this->county->id)
        ->assertDispatched('locationSelected', type: County::class, id: $this->county->id);
});

it('dispatches event when city is selected', function () {
    Livewire::test(LocationPicker::class)
        ->set('regionId', $this->region->id)
        ->set('countyId', $this->county->id)
        ->set('cityId', $this->city->id)
        ->assertDispatched('locationSelected', type: City::class, id: $this->city->id);
});

it('clears county and city when region changes', function () {
    Livewire::test(LocationPicker::class)
        ->set('regionId', $this->region->id)
        ->set('countyId', $this->county->id)
        ->set('cityId', $this->city->id)
        ->set('regionId', null)
        ->assertSet('countyId', null)
        ->assertSet('cityId', null);
});

it('clears city when county changes', function () {
    Livewire::test(LocationPicker::class)
        ->set('regionId', $this->region->id)
        ->set('countyId', $this->county->id)
        ->set('cityId', $this->city->id)
        ->set('countyId', null)
        ->assertSet('cityId', null);
});

it('initializes from city location', function () {
    Livewire::test(LocationPicker::class, [
        'locatableType' => City::class,
        'locatableId' => $this->city->id,
    ])
        ->assertSet('regionId', $this->region->id)
        ->assertSet('countyId', $this->county->id)
        ->assertSet('cityId', $this->city->id);
});

it('initializes from county location', function () {
    Livewire::test(LocationPicker::class, [
        'locatableType' => County::class,
        'locatableId' => $this->county->id,
    ])
        ->assertSet('regionId', $this->region->id)
        ->assertSet('countyId', $this->county->id)
        ->assertSet('cityId', null);
});

it('initializes from region location', function () {
    Livewire::test(LocationPicker::class, [
        'locatableType' => Region::class,
        'locatableId' => $this->region->id,
    ])
        ->assertSet('regionId', $this->region->id)
        ->assertSet('countyId', null)
        ->assertSet('cityId', null);
});

it('dispatches null when region is cleared', function () {
    Livewire::test(LocationPicker::class)
        ->set('regionId', $this->region->id)
        ->set('regionId', null)
        ->assertDispatched('locationSelected', type: null, id: null);
});

it('falls back to region when county is cleared', function () {
    Livewire::test(LocationPicker::class)
        ->set('regionId', $this->region->id)
        ->set('countyId', $this->county->id)
        ->set('countyId', null)
        ->assertDispatched('locationSelected', type: Region::class, id: $this->region->id);
});

it('falls back to county when city is cleared', function () {
    Livewire::test(LocationPicker::class)
        ->set('regionId', $this->region->id)
        ->set('countyId', $this->county->id)
        ->set('cityId', $this->city->id)
        ->set('cityId', null)
        ->assertDispatched('locationSelected', type: County::class, id: $this->county->id);
});
