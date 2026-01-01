@props(['index', 'block'])

<div class="p-4 pt-0 border-t border-steel-700/50 space-y-4">
    <div>
        <label class="block text-sm font-medium text-steel-300 mb-2">Carousel Images</label>

        @if(!empty($block['data']['images']))
            <div class="flex flex-wrap gap-3 mb-3">
                @foreach($block['data']['images'] as $imgIndex => $image)
                    <div class="relative">
                        <img src="{{ Storage::url($image['path']) }}" class="h-24 w-auto rounded-lg border border-steel-700" alt="{{ $image['alt'] ?? 'Carousel image' }}">
                        <button type="button" wire:click="removeCarouselImage({{ $index }}, {{ $imgIndex }})"
                            class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors shadow-lg">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                @endforeach
            </div>
        @endif

        @if(isset($blockImages[$block['id']]) && $blockImages[$block['id']])
            <div class="flex flex-wrap gap-3 mb-3">
                @php
                    $uploads = is_array($blockImages[$block['id']]) ? $blockImages[$block['id']] : [$blockImages[$block['id']]];
                @endphp
                @foreach($uploads as $uploadIndex => $upload)
                    @if($upload)
                        <div class="relative">
                            <img src="{{ $upload->temporaryUrl() }}" class="h-24 w-auto rounded-lg border border-accent-500" alt="New upload">
                            <span class="absolute bottom-1 left-1 px-1.5 py-0.5 bg-accent-500 text-white text-xs rounded">New</span>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

        <input type="file" wire:model="blockImages.{{ $block['id'] }}" accept="image/*" multiple
            class="block w-full text-sm text-steel-400
                file:mr-4 file:py-2 file:px-4
                file:rounded-lg file:border-0
                file:text-sm file:font-semibold
                file:bg-steel-700 file:text-steel-200
                hover:file:bg-steel-600 file:cursor-pointer
                file:transition-colors">
        <p class="mt-1 text-sm text-steel-500">Select multiple images for the carousel. Maximum 5MB each.</p>
    </div>
</div>
