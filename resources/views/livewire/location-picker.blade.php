<div class="space-y-4">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        {{-- County Select (primary choice) --}}
        <div>
            <x-input-label class="mb-2">County</x-input-label>
            <x-select wire:model.live="countyId">
                <option value="">Select a county...</option>
                @foreach($allCounties as $county)
                    <option value="{{ $county->id }}">{{ $county->name }} ({{ $county->region->name }})</option>
                @endforeach
            </x-select>
            <p class="mt-1 text-xs text-steel-500">Region auto-selects based on county</p>
        </div>

        {{-- Region Select (for region-wide guides) --}}
        <div>
            <x-input-label class="mb-2">Region <span class="text-steel-500">(for region-wide guides)</span></x-input-label>
            <x-select wire:model.live="regionId">
                <option value="">Select a region...</option>
                @foreach($regions as $region)
                    <option value="{{ $region->id }}">{{ $region->name }}</option>
                @endforeach
            </x-select>
        </div>
    </div>

    {{-- City Select (shown after county selected) --}}
    @if($cities->count() > 0)
        <div>
            <x-input-label class="mb-2">City <span class="text-steel-500">(optional - for city-specific guide)</span></x-input-label>
            <x-select wire:model.live="cityId">
                <option value="">County-wide guide</option>
                @foreach($cities as $city)
                    <option value="{{ $city->id }}">{{ $city->name }}</option>
                @endforeach
            </x-select>
        </div>
    @endif

    {{-- Selected Location Indicator --}}
    @if($this->selectedLocation)
        <div class="flex items-center gap-2 text-sm text-steel-300 bg-steel-800/50 rounded-lg px-3 py-2 border border-steel-700/50">
            <svg class="w-4 h-4 text-accent-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span>Guide location: <strong class="text-white">{{ $this->selectedLocation }}</strong></span>
        </div>
    @endif
</div>
