<div>
    @if($submitted)
        {{-- Success State --}}
        <div class="text-center py-12">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full {{ $isAdmin ? 'bg-green-500/20 border border-green-500/50' : 'bg-blue-500/20 border border-blue-500/50' }} mb-6">
                <svg class="w-8 h-8 {{ $isAdmin ? 'text-green-400' : 'text-blue-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            @if($isAdmin)
                <h3 class="text-2xl font-bold text-white mb-3">Changes Applied!</h3>
                <p class="text-steel-300 mb-6 max-w-md mx-auto">
                    Your changes to <strong class="text-white">"{{ $title }}"</strong>
                    have been applied immediately.
                </p>
            @else
                <h3 class="text-2xl font-bold text-white mb-3">Changes Submitted for Review!</h3>
                <p class="text-steel-300 mb-6 max-w-md mx-auto">
                    Your suggested changes to <strong class="text-white">"{{ $title }}"</strong>
                    have been submitted. An admin will review and approve them shortly.
                </p>
            @endif
            <div class="flex justify-center gap-4">
                @php
                    $content = \App\Models\Content::find($contentId);
                @endphp
                <a href="{{ route('guide.show', $content) }}" class="inline-flex items-center px-4 py-2 bg-steel-700 text-steel-200 rounded-lg hover:bg-steel-600 transition-colors">
                    View Guide
                </a>
                <a href="{{ route('guide.my-guides') }}" class="inline-flex items-center px-4 py-2 bg-accent-500 text-white rounded-lg hover:bg-accent-600 transition-colors">
                    My Guides
                </a>
            </div>
        </div>
    @else
        {{-- Non-admin notice --}}
        @if(!$isAdmin)
            <div class="mb-6 p-4 bg-gradient-to-r from-amber-500/20 to-amber-600/20 border border-amber-500/50 text-amber-200 rounded-xl flex items-center gap-3">
                <svg class="w-5 h-5 text-amber-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Your changes will be submitted for review. An admin will review and approve your edits before they are published.</span>
            </div>
        @endif

        {{-- Pending revision notice --}}
        @if($pendingRevision)
            <div class="mb-6 p-4 bg-gradient-to-r from-blue-500/20 to-blue-600/20 border border-blue-500/50 text-blue-200 rounded-xl">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-semibold">You have a pending revision</span>
                </div>
                <p class="text-sm text-blue-300">Submitted {{ $pendingRevision->created_at->diffForHumans() }}. Submitting new changes will replace this pending revision.</p>
            </div>
        @endif

        {{-- Form --}}
        <form wire:submit="submit" class="space-y-6">
            {{-- Error Summary --}}
            @if ($errors->any())
                <div class="p-4 bg-gradient-to-r from-red-500/20 to-red-600/20 border border-red-500/50 text-red-200 rounded-xl">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-semibold">Please fix the following errors:</span>
                    </div>
                    <ul class="list-disc list-inside space-y-1 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Title --}}
            <div>
                <x-input-label for="title" class="mb-2">Guide Title <span class="text-red-400">*</span></x-input-label>
                <x-text-input wire:model="title" id="title" placeholder="e.g., Best Hiking Trails in Hocking Hills"/>
                @error('title') <x-input-error :messages="$message" class="mt-1" /> @enderror
            </div>

            {{-- Location Picker --}}
            <div class="bg-steel-900/50 rounded-xl p-4 border border-steel-700/50">
                <h3 class="font-semibold text-steel-200 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-accent-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Location <span class="text-red-400">*</span>
                </h3>
                @livewire('location-picker', ['locatableType' => $locatableType, 'locatableId' => $locatableId])
                @error('locatableType') <x-input-error :messages="$message" class="mt-2" /> @enderror
            </div>

            {{-- Categories --}}
            <div class="bg-steel-900/50 rounded-xl p-4 border border-steel-700/50">
                <h3 class="font-semibold text-steel-200 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-accent-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    Categories <span class="text-red-400">*</span>
                </h3>
                <p class="text-sm text-steel-400 mb-4">Select one or more categories that best describe your guide.</p>
                @livewire('category-picker', ['categoryIds' => $categoryIds])
                @error('categoryIds') <x-input-error :messages="$message" class="mt-2" /> @enderror
            </div>

            {{-- Guide-Level Metadata --}}
            <div class="bg-steel-900/50 rounded-xl p-4 border border-steel-700/50">
                <h3 class="font-semibold text-steel-200 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-accent-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Guide Details <span class="text-steel-500 text-sm font-normal">(optional)</span>
                </h3>

                <div class="space-y-4">
                    {{-- Rating --}}
                    <div>
                        <label class="block text-sm font-medium text-steel-300 mb-2">Overall Rating</label>
                        <div class="flex items-center gap-1">
                            @for($star = 1; $star <= 5; $star++)
                                <button type="button" wire:click="setGuideRating({{ $star }})"
                                    class="text-3xl transition-colors {{ ($guideRating ?? 0) >= $star ? 'text-amber-400' : 'text-steel-600 hover:text-steel-500' }}">
                                    ★
                                </button>
                            @endfor
                            @if($guideRating)
                                <span class="ml-3 text-sm text-steel-400">{{ $guideRating }}/5 stars</span>
                            @endif
                        </div>
                        <p class="text-xs text-steel-500 mt-1">Click a star to rate, click again to clear</p>
                        @error('guideRating') <x-input-error :messages="$message" class="mt-1" /> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Website --}}
                        <div>
                            <label class="block text-sm font-medium text-steel-300 mb-1">Website</label>
                            <input type="url" wire:model="guideWebsite"
                                placeholder="https://example.com"
                                class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg p-2.5 text-sm">
                            @error('guideWebsite') <x-input-error :messages="$message" class="mt-1" /> @enderror
                        </div>

                        {{-- Address --}}
                        <div>
                            <label class="block text-sm font-medium text-steel-300 mb-1">Address</label>
                            <input type="text" wire:model="guideAddress"
                                placeholder="123 Main St, City, OH 12345"
                                class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg p-2.5 text-sm">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Excerpt/Summary --}}
            <div>
                <x-input-label for="excerpt" class="mb-2">Summary <span class="text-steel-500">(optional)</span></x-input-label>
                <textarea wire:model="excerpt" id="excerpt" rows="3"
                    placeholder="A brief summary of your guide that will appear in search results and previews..."
                    class="border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg shadow-inner p-2.5 px-4 text-base w-full transition-colors duration-200"></textarea>
                @error('excerpt') <x-input-error :messages="$message" class="mt-1" /> @enderror
            </div>

            {{-- Content Blocks --}}
            <div class="bg-steel-900/50 rounded-xl p-4 border border-steel-700/50">
                <h3 class="font-semibold text-steel-200 flex items-center gap-2 mb-4">
                    <svg class="w-5 h-5 text-accent-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                    </svg>
                    Content Blocks
                </h3>

                @if(!empty($blocks))
                    <div class="space-y-4"
                        x-data="{}"
                        x-init="
                            Sortable.create($el, {
                                handle: '.block-drag-handle',
                                animation: 150,
                                ghostClass: 'opacity-50',
                                onEnd: function(evt) {
                                    let items = Array.from($el.children).map(el => el.dataset.blockId);
                                    $wire.reorderBlocks(items);
                                }
                            });
                        ">
                        @foreach($blocks as $index => $block)
                            <div wire:key="block-{{ $block['id'] }}" data-block-id="{{ $block['id'] }}"
                                class="bg-steel-800/50 rounded-lg border border-steel-700/50 overflow-hidden">
                                {{-- Block Header --}}
                                <div class="flex items-center gap-3 p-3 cursor-pointer" wire:click="toggleBlock({{ $index }})">
                                    {{-- Drag Handle --}}
                                    <div class="block-drag-handle cursor-grab text-steel-500 hover:text-steel-300">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M7 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                                        </svg>
                                    </div>

                                    {{-- Block Type Icon & Label --}}
                                    <div class="flex items-center gap-2 flex-1">
                                        @switch($block['type'])
                                            @case('text')
                                                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                                                </svg>
                                                <span class="text-white font-medium">Text Block</span>
                                                @break
                                            @case('list')
                                                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                                                </svg>
                                                <span class="text-white font-medium">List Block</span>
                                                <span class="text-steel-500 text-sm">({{ count($block['data']['items'] ?? []) }} items)</span>
                                                @break
                                            @case('video')
                                                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                </svg>
                                                <span class="text-white font-medium">Video Block</span>
                                                @break
                                            @case('image')
                                                <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                <span class="text-white font-medium">Image Block</span>
                                                @break
                                            @case('carousel')
                                                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                <span class="text-white font-medium">Image Carousel</span>
                                                @break
                                        @endswitch
                                    </div>

                                    {{-- Expand/Collapse Icon --}}
                                    <svg class="w-5 h-5 text-steel-400 transition-transform {{ ($block['expanded'] ?? false) ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>

                                    {{-- Delete Button --}}
                                    <button type="button" wire:click.stop="removeBlock({{ $index }})"
                                        class="p-1.5 text-red-400 hover:text-red-300 hover:bg-red-500/20 rounded transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>

                                {{-- Block Body (Expanded) --}}
                                @if($block['expanded'] ?? false)
                                    @switch($block['type'])
                                        @case('text')
                                            @include('livewire.partials.block-text', ['index' => $index, 'block' => $block])
                                            @break
                                        @case('list')
                                            @include('livewire.partials.block-list', ['index' => $index, 'block' => $block])
                                            @break
                                        @case('video')
                                            @include('livewire.partials.block-video', ['index' => $index, 'block' => $block])
                                            @break
                                        @case('image')
                                            @include('livewire.partials.block-image', ['index' => $index, 'block' => $block])
                                            @break
                                        @case('carousel')
                                            @include('livewire.partials.block-carousel', ['index' => $index, 'block' => $block])
                                            @break
                                    @endswitch
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Add Block Dropdown --}}
                <div x-data="{ open: false }" class="relative mt-4 flex justify-center">
                    <button type="button" @click="open = !open"
                        class="inline-flex items-center gap-2 px-4 py-3 border-2 border-dashed border-steel-600 text-steel-400 rounded-lg hover:border-accent-500 hover:text-accent-400 transition-colors text-sm font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Block
                        <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="open" @click.away="open = false" x-transition
                        class="absolute bottom-full mb-2 w-48 bg-steel-800 border border-steel-700 rounded-lg shadow-xl z-10">
                        <button type="button" wire:click="addBlock('text')" @click="open = false"
                            class="w-full flex items-center gap-3 px-4 py-3 text-left text-steel-200 hover:bg-steel-700 transition-colors rounded-t-lg">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                            </svg>
                            Text Block
                        </button>
                        <button type="button" wire:click="addBlock('list')" @click="open = false"
                            class="w-full flex items-center gap-3 px-4 py-3 text-left text-steel-200 hover:bg-steel-700 transition-colors">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                            List Block
                        </button>
                        <button type="button" wire:click="addBlock('video')" @click="open = false"
                            class="w-full flex items-center gap-3 px-4 py-3 text-left text-steel-200 hover:bg-steel-700 transition-colors">
                            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            Video Block
                        </button>
                        <button type="button" wire:click="addBlock('image')" @click="open = false"
                            class="w-full flex items-center gap-3 px-4 py-3 text-left text-steel-200 hover:bg-steel-700 transition-colors">
                            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Image Block
                        </button>
                        <button type="button" wire:click="addBlock('carousel')" @click="open = false"
                            class="w-full flex items-center gap-3 px-4 py-3 text-left text-steel-200 hover:bg-steel-700 transition-colors rounded-b-lg">
                            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Image Carousel
                        </button>
                    </div>
                </div>
            </div>

            {{-- Submit Button --}}
            <div class="flex items-center justify-end gap-4 pt-4 border-t border-steel-700/50"
                x-data="{
                    syncAllEditors() {
                        if (typeof $ === 'undefined') return;
                        $('[id^=block-text-]').each(function() {
                            let $el = $(this);
                            if ($el.data('trumbowyg')) {
                                let blockId = this.id.replace('block-text-', '');
                                let blocks = $wire.blocks || [];
                                let idx = blocks.findIndex(b => b.id === blockId);
                                if (idx !== -1) {
                                    $wire.set('blocks.' + idx + '.data.content', $el.trumbowyg('html'));
                                }
                            }
                        });
                    },
                    syncAndSubmit() {
                        this.syncAllEditors();
                        $wire.submit();
                    }
                }">
                @php
                    $content = \App\Models\Content::find($contentId);
                @endphp
                <a href="{{ route('guide.show', $content) }}"
                    class="px-6 py-3 text-steel-300 hover:text-white transition-colors">
                    Cancel
                </a>
                <button type="button" @click="syncAndSubmit()" wire:loading.attr="disabled" wire:target="submit"
                    class="px-6 py-3 bg-gradient-to-r from-accent-500 to-accent-600 text-white font-semibold rounded-lg hover:from-accent-600 hover:to-accent-700 transition-all shadow-lg shadow-accent-500/25 flex items-center gap-2 disabled:opacity-50">
                    <span wire:loading.remove wire:target="submit" class="inline-flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        @if($isAdmin)
                            Save Changes
                        @else
                            Submit for Review
                        @endif
                    </span>
                    <span wire:loading wire:target="submit" class="inline-flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        @if($isAdmin)
                            Saving...
                        @else
                            Submitting...
                        @endif
                    </span>
                </button>
            </div>
        </form>
    @endif
</div>

@assets
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.30.0/dist/trumbowyg.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/ui/trumbowyg.min.css">
<style>
    .trumbowyg-dark .trumbowyg-box {
        border-color: #475569;
        background: #0f172a;
    }
    .trumbowyg-dark .trumbowyg-box .trumbowyg-editor {
        background: #0f172a;
        color: #e2e8f0;
    }
    .trumbowyg-dark .trumbowyg-button-pane {
        background: #1e293b;
        border-color: #475569;
    }
    .trumbowyg-dark .trumbowyg-button-pane button {
        color: #94a3b8;
    }
    .trumbowyg-dark .trumbowyg-button-pane button:hover {
        background: #334155;
        color: #e2e8f0;
    }
</style>
@endassets
