@props(['index', 'block'])

<div class="p-4 pt-0 border-t border-steel-700/50 space-y-4">
    <div>
        <label class="block text-sm font-medium text-steel-300 mb-2">Video URL <span class="text-red-400">*</span></label>
        <input type="url" wire:model="blocks.{{ $index }}.data.url"
            placeholder="https://www.youtube.com/watch?v=... or https://vimeo.com/..."
            class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg p-2.5 text-sm">
        <p class="text-xs text-steel-500 mt-1">YouTube, Vimeo, or other embed-friendly video URLs</p>
        @error("blocks.{$index}.data.url")
            <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
        @enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-steel-300 mb-2">Caption <span class="text-steel-500">(optional)</span></label>
        <input type="text" wire:model="blocks.{{ $index }}.data.caption"
            placeholder="Video caption or description"
            class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg p-2.5 text-sm">
    </div>

    @if(!empty($block['data']['url']))
        <div class="mt-3 p-3 bg-steel-900 rounded-lg border border-steel-700">
            <p class="text-xs text-steel-400 mb-2">Preview:</p>
            <div class="text-sm text-accent-400 break-all">{{ $block['data']['url'] }}</div>
        </div>
    @endif
</div>
