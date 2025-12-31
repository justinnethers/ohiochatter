@props(['index', 'block'])

<div class="p-4 pt-0 border-t border-steel-700/50 space-y-4">
    <div>
        <label class="block text-sm font-medium text-steel-300 mb-2">Image</label>

        @if(!empty($block['data']['path']))
            <div class="relative inline-block mb-3">
                <img src="{{ Storage::url($block['data']['path']) }}" class="h-32 w-auto rounded-lg border border-steel-700" alt="{{ $block['data']['alt'] ?? 'Block image' }}">
            </div>
        @elseif(isset($blockImages[$block['id']]) && $blockImages[$block['id']])
            <div class="relative inline-block mb-3">
                <img src="{{ $blockImages[$block['id']]->temporaryUrl() }}" class="h-32 w-auto rounded-lg border border-steel-700" alt="Preview">
            </div>
        @else
            <input type="file" wire:model="blockImages.{{ $block['id'] }}" accept="image/*"
                class="block w-full text-sm text-steel-400
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-lg file:border-0
                    file:text-sm file:font-semibold
                    file:bg-accent-500 file:text-white
                    hover:file:bg-accent-600 file:cursor-pointer
                    file:transition-colors">
            <p class="mt-1 text-sm text-steel-500">Maximum 5MB. JPG, PNG, or GIF.</p>
        @endif
    </div>

    <div>
        <label class="block text-sm font-medium text-steel-300 mb-2">Alt Text <span class="text-steel-500">(optional)</span></label>
        <input type="text" wire:model="blocks.{{ $index }}.data.alt"
            placeholder="Describe the image for accessibility"
            class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg p-2.5 text-sm">
    </div>

    <div>
        <label class="block text-sm font-medium text-steel-300 mb-2">Caption <span class="text-steel-500">(optional)</span></label>
        <input type="text" wire:model="blocks.{{ $index }}.data.caption"
            placeholder="Image caption"
            class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg p-2.5 text-sm">
    </div>
</div>
