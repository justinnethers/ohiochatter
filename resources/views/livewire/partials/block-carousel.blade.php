@props(['index', 'block'])

<div class="p-4 pt-0 border-t border-steel-700/50 space-y-4">
    <div>
        <label class="block text-sm font-medium text-steel-300 mb-2">Carousel Images</label>

        @if(!empty($block['data']['images']))
            <div class="flex flex-wrap gap-3 mb-3">
                @foreach($block['data']['images'] as $imgIndex => $image)
                    <div class="relative">
                        <img src="{{ Storage::url($image['path']) }}" class="h-24 w-auto rounded-lg border border-steel-700" alt="{{ $image['alt'] ?? 'Carousel image' }}">
                    </div>
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
