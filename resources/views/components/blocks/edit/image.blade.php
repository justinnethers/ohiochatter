@props([
    'block',
    'index' => 0,
    'path' => '',
    'nested' => false,
    'imageKey' => null,
    'removeAction' => null,
    'removeUploadAction' => null,
])

@php
    $imageProperty = $nested ? "nestedBlockImages.{$imageKey}" : "blockImages.{$block['id']}";
@endphp

<div class="space-y-2">
    @if(!empty($block['data']['path']))
        <div class="relative inline-block">
            <img src="{{ Storage::url($block['data']['path']) }}" class="h-24 w-auto rounded-lg border border-steel-700" alt="">
            @if($removeAction)
                <button type="button" wire:click="{{ $removeAction }}"
                    class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors shadow-lg">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            @endif
        </div>
    @elseif($imageKey && isset($nestedBlockImages[$imageKey]) && $nestedBlockImages[$imageKey])
        <div class="relative inline-block">
            <img src="{{ $nestedBlockImages[$imageKey]->temporaryUrl() }}" class="h-24 w-auto rounded-lg border border-accent-500" alt="Preview">
            @if($removeUploadAction)
                <button type="button" wire:click="{{ $removeUploadAction }}"
                    class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors shadow-lg">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            @endif
        </div>
    @elseif(!$nested && isset($blockImages[$block['id']]) && $blockImages[$block['id']])
        <div class="relative inline-block">
            <img src="{{ $blockImages[$block['id']]->temporaryUrl() }}" class="h-24 w-auto rounded-lg border border-accent-500" alt="Preview">
            @if($removeUploadAction)
                <button type="button" wire:click="{{ $removeUploadAction }}"
                    class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors shadow-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            @endif
        </div>
    @else
        <input type="file" wire:model="{{ $imageProperty }}" accept="image/*"
            class="block w-full text-sm text-steel-400
                file:mr-4 file:py-{{ $nested ? '1.5' : '2' }} file:px-{{ $nested ? '3' : '4' }}
                file:rounded-lg file:border-0
                file:text-sm file:font-{{ $nested ? 'medium' : 'semibold' }}
                file:bg-accent-500 file:text-white
                hover:file:bg-accent-600 file:cursor-pointer
                file:transition-colors">
        @if(!$nested)
            <p class="mt-1 text-sm text-steel-500">Maximum 5MB. JPG, PNG, or GIF.</p>
        @endif
    @endif

    @if(!$nested)
        <input type="text" wire:model="blocks.{{ $path }}.data.alt"
            placeholder="Alt text (optional)"
            class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg p-2 text-sm">
    @endif

    <input type="text" wire:model="blocks.{{ $path }}.data.caption"
        placeholder="Caption (optional)"
        class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg p-2 text-sm">
</div>
