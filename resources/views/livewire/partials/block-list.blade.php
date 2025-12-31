@props(['index', 'block'])

<div class="p-4 pt-0 border-t border-steel-700/50 space-y-4">
    {{-- List Settings --}}
    <div class="flex flex-wrap items-center gap-4 pb-3 border-b border-steel-700/50">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-steel-300 mb-1">List Title <span class="text-steel-500">(optional)</span></label>
            <input type="text" wire:model="blocks.{{ $index }}.data.title"
                placeholder="e.g., Top 5 Best Restaurants"
                class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg p-2.5 text-sm">
        </div>

        <div class="flex items-center gap-4">
            <label class="flex items-center gap-2 cursor-pointer text-sm">
                <input type="checkbox" wire:model.live="blocks.{{ $index }}.data.ranked"
                    class="w-4 h-4 rounded border-steel-600 bg-steel-800 text-accent-500 focus:ring-accent-500/20 focus:ring-offset-0">
                <span class="text-steel-300">Ranked</span>
            </label>

            @if($block['data']['ranked'] ?? true)
                <label class="flex items-center gap-2 cursor-pointer text-sm">
                    <input type="checkbox" wire:model.live="blocks.{{ $index }}.data.countdown"
                        class="w-4 h-4 rounded border-steel-600 bg-steel-800 text-accent-500 focus:ring-accent-500/20 focus:ring-offset-0">
                    <span class="text-steel-300">Countdown</span>
                    <span class="text-steel-500 text-xs">(#5 → #1)</span>
                </label>
            @endif
        </div>
    </div>

    {{-- List Items --}}
    <div class="space-y-3"
        x-data="{}"
        x-init="
            Sortable.create($el, {
                handle: '.drag-handle',
                animation: 150,
                ghostClass: 'opacity-50',
                onEnd: function(evt) {
                    let items = Array.from($el.children).map(el => el.dataset.itemId);
                    $wire.reorderListItemsInBlock({{ $index }}, items);
                }
            });
        ">
        @foreach(($block['data']['items'] ?? []) as $itemIndex => $item)
            <div wire:key="block-{{ $block['id'] }}-item-{{ $item['id'] }}" data-item-id="{{ $item['id'] }}"
                class="bg-steel-900/50 rounded-lg border border-steel-700/50 overflow-hidden">
                {{-- Item Header --}}
                <div class="flex items-center gap-3 p-3 cursor-pointer" wire:click="toggleListItemInBlock({{ $index }}, {{ $itemIndex }})">
                    {{-- Drag Handle --}}
                    <div class="drag-handle cursor-grab text-steel-500 hover:text-steel-300">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M7 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                        </svg>
                    </div>

                    {{-- Rank Number --}}
                    @if($block['data']['ranked'] ?? true)
                        @php
                            $itemCount = count($block['data']['items'] ?? []);
                            $displayNumber = ($block['data']['countdown'] ?? false)
                                ? $itemCount - $itemIndex
                                : $itemIndex + 1;
                        @endphp
                        <span class="flex items-center justify-center w-8 h-8 rounded-full bg-accent-500/20 text-accent-400 font-bold text-sm">
                            #{{ $displayNumber }}
                        </span>
                    @endif

                    {{-- Title Preview --}}
                    <span class="flex-1 text-white font-medium truncate">
                        {{ $item['title'] ?: 'Untitled Item' }}
                    </span>

                    {{-- Expand/Collapse Icon --}}
                    <svg class="w-5 h-5 text-steel-400 transition-transform {{ ($item['expanded'] ?? false) ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>

                    {{-- Delete Button --}}
                    <button type="button" wire:click.stop="removeListItemFromBlock({{ $index }}, {{ $itemIndex }})"
                        class="p-1.5 text-red-400 hover:text-red-300 hover:bg-red-500/20 rounded transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>

                {{-- Item Body (Expanded) --}}
                @if($item['expanded'] ?? false)
                    <div class="p-4 pt-0 space-y-4 border-t border-steel-700/50">
                        {{-- Title --}}
                        <div>
                            <label class="block text-sm font-medium text-steel-300 mb-1">Title <span class="text-red-400">*</span></label>
                            <input type="text" wire:model="blocks.{{ $index }}.data.items.{{ $itemIndex }}.title"
                                placeholder="e.g., Best Pizza Place"
                                class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg p-2.5 text-sm">
                            @error("blocks.{$index}.data.items.{$itemIndex}.title")
                                <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Description --}}
                        <div>
                            <label class="block text-sm font-medium text-steel-300 mb-1">Description <span class="text-red-400">*</span></label>
                            <textarea wire:model="blocks.{{ $index }}.data.items.{{ $itemIndex }}.description" rows="3"
                                placeholder="Describe why this made the list..."
                                class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg p-2.5 text-sm"></textarea>
                            @error("blocks.{$index}.data.items.{$itemIndex}.description")
                                <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Website --}}
                            <div>
                                <label class="block text-sm font-medium text-steel-300 mb-1">Website</label>
                                <input type="url" wire:model="blocks.{{ $index }}.data.items.{{ $itemIndex }}.website"
                                    placeholder="https://example.com"
                                    class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg p-2.5 text-sm">
                            </div>

                            {{-- Address --}}
                            <div>
                                <label class="block text-sm font-medium text-steel-300 mb-1">Address</label>
                                <input type="text" wire:model="blocks.{{ $index }}.data.items.{{ $itemIndex }}.address"
                                    placeholder="123 Main St, City, OH"
                                    class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg p-2.5 text-sm">
                            </div>
                        </div>

                        {{-- Rating --}}
                        <div>
                            <label class="block text-sm font-medium text-steel-300 mb-2">Rating</label>
                            <div class="flex items-center gap-1">
                                @for($star = 1; $star <= 5; $star++)
                                    <button type="button" wire:click="setListItemRatingInBlock({{ $index }}, {{ $itemIndex }}, {{ ($item['rating'] ?? 0) === $star ? 'null' : $star }})"
                                        class="text-2xl transition-colors {{ ($item['rating'] ?? 0) >= $star ? 'text-amber-400' : 'text-steel-600 hover:text-steel-500' }}">
                                        ★
                                    </button>
                                @endfor
                                @if($item['rating'] ?? null)
                                    <span class="ml-2 text-sm text-steel-400">{{ $item['rating'] }}/5</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Add Item Button --}}
    <button type="button" wire:click="addListItemToBlock({{ $index }})"
        class="w-full py-3 border-2 border-dashed border-steel-600 rounded-lg text-steel-400 hover:text-steel-300 hover:border-steel-500 transition-colors flex items-center justify-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add List Item
    </button>
</div>
