@props(['parentIndex', 'itemIndex', 'nestedIndex', 'nestedBlock'])

<div class="space-y-3">
    {{-- List Settings --}}
    <div class="flex flex-wrap items-center gap-3 pb-2 border-b border-steel-700/30">
        <div class="flex-1 min-w-[150px]">
            <input type="text" wire:model="blocks.{{ $parentIndex }}.data.items.{{ $itemIndex }}.blocks.{{ $nestedIndex }}.data.title"
                placeholder="List title (optional)"
                class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg p-2 text-sm">
        </div>

        <label class="flex items-center gap-1.5 cursor-pointer text-xs">
            <input type="checkbox" wire:model.live="blocks.{{ $parentIndex }}.data.items.{{ $itemIndex }}.blocks.{{ $nestedIndex }}.data.ranked"
                class="w-3.5 h-3.5 rounded border-steel-600 bg-steel-800 text-accent-500 focus:ring-accent-500/20 focus:ring-offset-0">
            <span class="text-steel-300">Ranked</span>
        </label>

        @if($nestedBlock['data']['ranked'] ?? true)
            <label class="flex items-center gap-1.5 cursor-pointer text-xs">
                <input type="checkbox" wire:model.live="blocks.{{ $parentIndex }}.data.items.{{ $itemIndex }}.blocks.{{ $nestedIndex }}.data.countdown"
                    class="w-3.5 h-3.5 rounded border-steel-600 bg-steel-800 text-accent-500 focus:ring-accent-500/20 focus:ring-offset-0">
                <span class="text-steel-300">Countdown</span>
            </label>
        @endif
    </div>

    {{-- Nested List Items --}}
    @if(!empty($nestedBlock['data']['items']))
        <div class="space-y-2">
            @foreach($nestedBlock['data']['items'] as $nestedItemIndex => $nestedItem)
                @php
                    $itemCount = count($nestedBlock['data']['items']);
                    $displayNumber = ($nestedBlock['data']['countdown'] ?? false)
                        ? $itemCount - $nestedItemIndex
                        : $nestedItemIndex + 1;
                @endphp
                <div wire:key="nested-list-item-{{ $parentIndex }}-{{ $itemIndex }}-{{ $nestedIndex }}-{{ $nestedItem['id'] ?? $nestedItemIndex }}"
                    class="bg-steel-800/30 rounded-lg border border-steel-700/50 overflow-hidden">
                    {{-- Item Header --}}
                    <div class="flex items-center gap-2 p-2 cursor-pointer" wire:click="toggleNestedListItem({{ $parentIndex }}, {{ $itemIndex }}, {{ $nestedIndex }}, {{ $nestedItemIndex }})">
                        @if($nestedBlock['data']['ranked'] ?? true)
                            <span class="shrink-0 w-6 h-6 rounded-full bg-accent-500/20 text-accent-400 text-xs font-bold flex items-center justify-center">
                                #{{ $displayNumber }}
                            </span>
                        @endif
                        <span class="flex-1 text-white text-sm font-medium truncate">
                            {{ $nestedItem['title'] ?: 'Untitled Item' }}
                        </span>
                        <svg class="w-4 h-4 text-steel-400 transition-transform {{ ($nestedItem['expanded'] ?? false) ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                        <button type="button" wire:click.stop="removeNestedListItem({{ $parentIndex }}, {{ $itemIndex }}, {{ $nestedIndex }}, {{ $nestedItemIndex }})"
                            class="shrink-0 p-1 text-red-400 hover:text-red-300 hover:bg-red-500/20 rounded transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Item Body (Expanded) --}}
                    @if($nestedItem['expanded'] ?? false)
                        <div class="p-3 pt-0 space-y-3 border-t border-steel-700/30">
                            {{-- Title --}}
                            <div>
                                <label class="block text-xs font-medium text-steel-300 mb-1">Title <span class="text-red-400">*</span></label>
                                <input type="text" wire:model="blocks.{{ $parentIndex }}.data.items.{{ $itemIndex }}.blocks.{{ $nestedIndex }}.data.items.{{ $nestedItemIndex }}.title"
                                    placeholder="Item title"
                                    class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg p-2 text-sm">
                            </div>

                            {{-- Description --}}
                            <div>
                                <label class="block text-xs font-medium text-steel-300 mb-1">Description <span class="text-red-400">*</span></label>
                                <textarea wire:model="blocks.{{ $parentIndex }}.data.items.{{ $itemIndex }}.blocks.{{ $nestedIndex }}.data.items.{{ $nestedItemIndex }}.description"
                                    placeholder="Description"
                                    rows="2"
                                    class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg p-2 text-sm"></textarea>
                            </div>

                            {{-- Website & Address --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-steel-300 mb-1">Website</label>
                                    <input type="url" wire:model="blocks.{{ $parentIndex }}.data.items.{{ $itemIndex }}.blocks.{{ $nestedIndex }}.data.items.{{ $nestedItemIndex }}.website"
                                        placeholder="https://example.com"
                                        class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg p-2 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-steel-300 mb-1">Address</label>
                                    <input type="text" wire:model="blocks.{{ $parentIndex }}.data.items.{{ $itemIndex }}.blocks.{{ $nestedIndex }}.data.items.{{ $nestedItemIndex }}.address"
                                        placeholder="123 Main St, City, OH"
                                        class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg p-2 text-sm">
                                </div>
                            </div>

                            {{-- Rating --}}
                            <div>
                                <label class="block text-xs font-medium text-steel-300 mb-1">Rating</label>
                                <div class="flex items-center gap-0.5">
                                    @for($star = 1; $star <= 5; $star++)
                                        <button type="button" wire:click="setNestedListItemRating({{ $parentIndex }}, {{ $itemIndex }}, {{ $nestedIndex }}, {{ $nestedItemIndex }}, {{ ($nestedItem['rating'] ?? 0) === $star ? 'null' : $star }})"
                                            class="text-xl transition-colors {{ ($nestedItem['rating'] ?? 0) >= $star ? 'text-amber-400' : 'text-steel-600 hover:text-steel-500' }}">
                                            ★
                                        </button>
                                    @endfor
                                    @if($nestedItem['rating'] ?? null)
                                        <span class="ml-2 text-xs text-steel-400">{{ $nestedItem['rating'] }}/5</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Image --}}
                            <div>
                                <label class="block text-xs font-medium text-steel-300 mb-1">Image</label>
                                @php
                                    $nestedListImageKey = "nested_list_{$parentIndex}_{$itemIndex}_{$nestedIndex}_{$nestedItemIndex}";
                                @endphp
                                @if(!empty($nestedItem['image']))
                                    <div class="relative inline-block">
                                        <img src="{{ Storage::url($nestedItem['image']) }}" class="h-20 w-auto rounded-lg border border-steel-700" alt="">
                                        <button type="button" wire:click="removeNestedListItemImage({{ $parentIndex }}, {{ $itemIndex }}, {{ $nestedIndex }}, {{ $nestedItemIndex }})"
                                            class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors shadow-lg">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                @elseif(isset($nestedBlockImages[$nestedListImageKey]) && $nestedBlockImages[$nestedListImageKey])
                                    <div class="relative inline-block">
                                        <img src="{{ $nestedBlockImages[$nestedListImageKey]->temporaryUrl() }}" class="h-20 w-auto rounded-lg border border-accent-500" alt="Preview">
                                        <button type="button" wire:click="$set('nestedBlockImages.{{ $nestedListImageKey }}', null)"
                                            class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors shadow-lg">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                @else
                                    <input type="file" wire:model="nestedBlockImages.{{ $nestedListImageKey }}" accept="image/*"
                                        class="block w-full text-sm text-steel-400
                                            file:mr-3 file:py-1.5 file:px-3
                                            file:rounded-lg file:border-0
                                            file:text-xs file:font-medium
                                            file:bg-steel-700 file:text-steel-200
                                            hover:file:bg-steel-600 file:cursor-pointer
                                            file:transition-colors">
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    {{-- Add Nested List Item Button --}}
    <button type="button" wire:click="addNestedListItem({{ $parentIndex }}, {{ $itemIndex }}, {{ $nestedIndex }})"
        class="w-full py-2 border border-dashed border-steel-600 rounded-lg text-steel-400 hover:text-steel-300 hover:border-steel-500 transition-colors flex items-center justify-center gap-2 text-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add Item
    </button>
</div>
