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

                        {{-- Nested Blocks --}}
                        <div class="pt-4 border-t border-steel-700/50">
                            <label class="block text-sm font-medium text-steel-300 mb-3">Additional Content <span class="text-steel-500">(optional)</span></label>

                            @if(!empty($item['blocks']))
                                <div class="space-y-3 mb-4">
                                    @foreach($item['blocks'] as $nestedIndex => $nestedBlock)
                                        <div wire:key="nested-block-{{ $block['id'] }}-{{ $item['id'] }}-{{ $nestedBlock['id'] }}"
                                            class="bg-steel-800/50 rounded-lg border border-steel-600/50 overflow-hidden">
                                            {{-- Nested Block Header --}}
                                            <div class="flex items-center gap-2 p-2 bg-steel-800/80">
                                                @switch($nestedBlock['type'])
                                                    @case('text')
                                                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                                                        </svg>
                                                        <span class="text-steel-200 text-sm font-medium flex-1">Text</span>
                                                        @break
                                                    @case('image')
                                                        <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                        </svg>
                                                        <span class="text-steel-200 text-sm font-medium flex-1">Image</span>
                                                        @break
                                                    @case('carousel')
                                                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                        </svg>
                                                        <span class="text-steel-200 text-sm font-medium flex-1">Carousel</span>
                                                        @break
                                                    @case('list')
                                                        <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                                        </svg>
                                                        <span class="text-steel-200 text-sm font-medium flex-1">List</span>
                                                        @break
                                                    @case('video')
                                                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                        </svg>
                                                        <span class="text-steel-200 text-sm font-medium flex-1">Video</span>
                                                        @break
                                                @endswitch

                                                <button type="button" wire:click="removeBlockFromListItem({{ $index }}, {{ $itemIndex }}, {{ $nestedIndex }})"
                                                    class="p-1 text-red-400 hover:text-red-300 hover:bg-red-500/20 rounded transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </div>

                                            {{-- Nested Block Content --}}
                                            <div class="p-3">
                                                @php
                                                    $nestedImageKey = $this->getNestedBlockImageKey($index, $itemIndex, $nestedIndex);
                                                @endphp
                                                @switch($nestedBlock['type'])
                                                    @case('text')
                                                        <textarea wire:model="blocks.{{ $index }}.data.items.{{ $itemIndex }}.blocks.{{ $nestedIndex }}.data.content"
                                                            rows="4"
                                                            placeholder="Enter additional text content..."
                                                            class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg p-2.5 text-sm"></textarea>
                                                        @break
                                                    @case('image')
                                                        <div class="space-y-2">
                                                            @if(!empty($nestedBlock['data']['path']))
                                                                <div class="relative inline-block">
                                                                    <img src="{{ Storage::url($nestedBlock['data']['path']) }}" class="h-24 w-auto rounded-lg border border-steel-700" alt="">
                                                                    <button type="button" wire:click="removeNestedBlockImage({{ $index }}, {{ $itemIndex }}, {{ $nestedIndex }})"
                                                                        class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors shadow-lg">
                                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                                        </svg>
                                                                    </button>
                                                                </div>
                                                            @elseif(isset($nestedBlockImages[$nestedImageKey]) && $nestedBlockImages[$nestedImageKey])
                                                                <div class="relative inline-block">
                                                                    <img src="{{ $nestedBlockImages[$nestedImageKey]->temporaryUrl() }}" class="h-24 w-auto rounded-lg border border-accent-500" alt="Preview">
                                                                    <button type="button" wire:click="removeNestedBlockImageUpload({{ $index }}, {{ $itemIndex }}, {{ $nestedIndex }})"
                                                                        class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors shadow-lg">
                                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                                        </svg>
                                                                    </button>
                                                                </div>
                                                            @else
                                                                <input type="file" wire:model="nestedBlockImages.{{ $nestedImageKey }}" accept="image/*"
                                                                    class="block w-full text-sm text-steel-400
                                                                        file:mr-4 file:py-1.5 file:px-3
                                                                        file:rounded-lg file:border-0
                                                                        file:text-sm file:font-medium
                                                                        file:bg-accent-500 file:text-white
                                                                        hover:file:bg-accent-600 file:cursor-pointer
                                                                        file:transition-colors">
                                                            @endif
                                                            <input type="text" wire:model="blocks.{{ $index }}.data.items.{{ $itemIndex }}.blocks.{{ $nestedIndex }}.data.caption"
                                                                placeholder="Caption (optional)"
                                                                class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg p-2 text-sm">
                                                        </div>
                                                        @break
                                                    @case('video')
                                                        <div class="space-y-2">
                                                            <input type="url" wire:model="blocks.{{ $index }}.data.items.{{ $itemIndex }}.blocks.{{ $nestedIndex }}.data.url"
                                                                placeholder="YouTube or video URL"
                                                                class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg p-2 text-sm">
                                                            <input type="text" wire:model="blocks.{{ $index }}.data.items.{{ $itemIndex }}.blocks.{{ $nestedIndex }}.data.caption"
                                                                placeholder="Caption (optional)"
                                                                class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg p-2 text-sm">
                                                        </div>
                                                        @break
                                                    @case('carousel')
                                                        <div class="space-y-2">
                                                            @if(!empty($nestedBlock['data']['images']))
                                                                <div class="flex flex-wrap gap-2">
                                                                    @foreach($nestedBlock['data']['images'] as $imgIndex => $image)
                                                                        <div class="relative">
                                                                            <img src="{{ Storage::url($image['path']) }}" class="h-16 w-auto rounded-lg border border-steel-700" alt="">
                                                                            <button type="button" wire:click="removeNestedCarouselImage({{ $index }}, {{ $itemIndex }}, {{ $nestedIndex }}, {{ $imgIndex }})"
                                                                                class="absolute -top-1.5 -right-1.5 w-4 h-4 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors shadow-lg">
                                                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                                                </svg>
                                                                            </button>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                            @if(isset($nestedBlockImages[$nestedImageKey]) && $nestedBlockImages[$nestedImageKey])
                                                                <div class="flex flex-wrap gap-2">
                                                                    @php
                                                                        $uploads = is_array($nestedBlockImages[$nestedImageKey]) ? $nestedBlockImages[$nestedImageKey] : [$nestedBlockImages[$nestedImageKey]];
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
                                                            @endif
                                                            <input type="file" wire:model="nestedBlockImages.{{ $nestedImageKey }}" accept="image/*" multiple
                                                                class="block w-full text-sm text-steel-400
                                                                    file:mr-4 file:py-1.5 file:px-3
                                                                    file:rounded-lg file:border-0
                                                                    file:text-sm file:font-medium
                                                                    file:bg-steel-700 file:text-steel-200
                                                                    hover:file:bg-steel-600 file:cursor-pointer
                                                                    file:transition-colors">
                                                            <p class="text-xs text-steel-500">Select multiple images for the carousel</p>
                                                        </div>
                                                        @break
                                                    @case('list')
                                                        @include('livewire.partials.nested-list-block', [
                                                            'parentIndex' => $index,
                                                            'itemIndex' => $itemIndex,
                                                            'nestedIndex' => $nestedIndex,
                                                            'nestedBlock' => $nestedBlock
                                                        ])
                                                        @break
                                                @endswitch
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Add Nested Block Dropdown --}}
                            <div x-data="{ open: false }" class="relative">
                                <button type="button" @click="open = !open"
                                    class="inline-flex items-center gap-2 px-3 py-2 border border-dashed border-steel-600 text-steel-400 rounded-lg hover:border-steel-500 hover:text-steel-300 transition-colors text-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Add Content Block
                                </button>

                                <div x-show="open" @click.away="open = false" x-transition
                                    class="absolute left-0 bottom-full mb-2 w-40 bg-steel-800 border border-steel-700 rounded-lg shadow-xl z-20">
                                    <button type="button" wire:click="addBlockToListItem({{ $index }}, {{ $itemIndex }}, 'text')" @click="open = false"
                                        class="w-full flex items-center gap-2 px-3 py-2 text-left text-steel-200 hover:bg-steel-700 transition-colors rounded-t-lg text-sm">
                                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                                        </svg>
                                        Text
                                    </button>
                                    <button type="button" wire:click="addBlockToListItem({{ $index }}, {{ $itemIndex }}, 'image')" @click="open = false"
                                        class="w-full flex items-center gap-2 px-3 py-2 text-left text-steel-200 hover:bg-steel-700 transition-colors text-sm">
                                        <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        Image
                                    </button>
                                    <button type="button" wire:click="addBlockToListItem({{ $index }}, {{ $itemIndex }}, 'video')" @click="open = false"
                                        class="w-full flex items-center gap-2 px-3 py-2 text-left text-steel-200 hover:bg-steel-700 transition-colors text-sm">
                                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                        Video
                                    </button>
                                    <button type="button" wire:click="addBlockToListItem({{ $index }}, {{ $itemIndex }}, 'carousel')" @click="open = false"
                                        class="w-full flex items-center gap-2 px-3 py-2 text-left text-steel-200 hover:bg-steel-700 transition-colors text-sm">
                                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        Carousel
                                    </button>
                                    <button type="button" wire:click="addBlockToListItem({{ $index }}, {{ $itemIndex }}, 'list')" @click="open = false"
                                        class="w-full flex items-center gap-2 px-3 py-2 text-left text-steel-200 hover:bg-steel-700 transition-colors rounded-b-lg text-sm">
                                        <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                        </svg>
                                        List
                                    </button>
                                </div>
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
