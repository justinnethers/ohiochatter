@props([
    'block',
    'index' => 0,
    'path' => '',
    'nested' => false,
    'imageKey' => null,
    'removeImageAction' => null,
])

@php
    $imageProperty = $nested ? "nestedBlockImages.{$imageKey}" : "blockImages.{$block['id']}";
@endphp

<div class="space-y-2">
    @if(!empty($block['data']['images']))
        <div class="flex flex-wrap gap-{{ $nested ? '2' : '3' }}">
            @foreach($block['data']['images'] as $imgIndex => $image)
                <div class="relative">
                    <img src="{{ Storage::url($image['path']) }}" class="h-{{ $nested ? '16' : '24' }} w-auto rounded-lg border border-steel-700" alt="">
                    @if($removeImageAction)
                        <button type="button" wire:click="{{ str_replace('INDEX', $imgIndex, $removeImageAction) }}"
                            class="absolute -top-{{ $nested ? '1.5' : '2' }} -right-{{ $nested ? '1.5' : '2' }} w-{{ $nested ? '4' : '5' }} h-{{ $nested ? '4' : '5' }} bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors shadow-lg">
                            <svg class="w-{{ $nested ? '2.5' : '3' }} h-{{ $nested ? '2.5' : '3' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if($imageKey && isset($nestedBlockImages[$imageKey]) && $nestedBlockImages[$imageKey])
        <div class="flex flex-wrap gap-2">
            @php
                $uploads = is_array($nestedBlockImages[$imageKey]) ? $nestedBlockImages[$imageKey] : [$nestedBlockImages[$imageKey]];
            @endphp
            @foreach($uploads as $upload)
                @if($upload)
                    <div class="relative">
                        <img src="{{ $upload->temporaryUrl() }}" class="h-16 w-auto rounded-lg border border-accent-500" alt="New">
                        <span class="absolute bottom-0.5 left-0.5 px-1 py-0.5 bg-accent-500 text-white text-xs rounded">New</span>
                    </div>
                @endif
            @endforeach
        </div>
    @elseif(!$nested && isset($blockImages[$block['id']]) && $blockImages[$block['id']])
        <div class="flex flex-wrap gap-3">
            @php
                $uploads = is_array($blockImages[$block['id']]) ? $blockImages[$block['id']] : [$blockImages[$block['id']]];
            @endphp
            @foreach($uploads as $upload)
                @if($upload)
                    <div class="relative">
                        <img src="{{ $upload->temporaryUrl() }}" class="h-24 w-auto rounded-lg border border-accent-500" alt="New upload">
                        <span class="absolute bottom-1 left-1 px-1.5 py-0.5 bg-accent-500 text-white text-xs rounded">New</span>
                    </div>
                @endif
            @endforeach
        </div>
    @endif

    <input type="file" wire:model="{{ $imageProperty }}" accept="image/*" multiple
        class="block w-full text-sm text-steel-400
            file:mr-4 file:py-{{ $nested ? '1.5' : '2' }} file:px-{{ $nested ? '3' : '4' }}
            file:rounded-lg file:border-0
            file:text-sm file:font-{{ $nested ? 'medium' : 'semibold' }}
            file:bg-steel-700 file:text-steel-200
            hover:file:bg-steel-600 file:cursor-pointer
            file:transition-colors">
    <p class="text-{{ $nested ? 'xs' : 'sm' }} text-steel-500">Select multiple images for the carousel{{ $nested ? '' : '. Maximum 5MB each.' }}</p>
</div>
