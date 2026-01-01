@props(['block', 'index' => 0, 'path' => '', 'nested' => false])

<div class="space-y-2">
    <input type="url" wire:model="blocks.{{ $path }}.data.url"
        placeholder="YouTube or video URL"
        class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg p-2 text-sm">
    <input type="text" wire:model="blocks.{{ $path }}.data.caption"
        placeholder="Caption (optional)"
        class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg p-2 text-sm">
</div>
