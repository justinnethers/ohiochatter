<div>
    @if($submitted)
        {{-- Success State --}}
        <div class="text-center py-12">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-500/20 border border-green-500/50 mb-6">
                <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-white mb-3">Guide Submitted!</h3>
            <p class="text-steel-300 mb-6 max-w-md mx-auto">
                Thank you for contributing to OhioChatter! Your guide
                <strong class="text-white">"{{ $createdContent->title }}"</strong>
                has been submitted for review. Our team will review it shortly.
            </p>
            <div class="flex justify-center gap-4">
                <a href="{{ route('guide.index') }}" class="inline-flex items-center px-4 py-2 bg-steel-700 text-steel-200 rounded-lg hover:bg-steel-600 transition-colors">
                    Browse Guides
                </a>
                <button wire:click="resetForm" type="button"
                    class="inline-flex items-center px-4 py-2 bg-accent-500 text-white rounded-lg hover:bg-accent-600 transition-colors">
                    Create Another Guide
                </button>
            </div>
        </div>
    @else
        {{-- Draft Saved Notice --}}
        @if($savedDraft)
            <div class="mb-6 p-4 bg-gradient-to-r from-green-500/20 to-green-600/20 border border-green-500/50 text-green-200 rounded-xl flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Draft saved! You can close this page and continue later.</span>
                </div>
                <a href="{{ route('guide.drafts') }}" class="text-sm text-green-300 hover:text-green-200 underline">View My Drafts</a>
            </div>
        @endif

        {{-- Editing Draft Notice --}}
        @if($draftId && !$savedDraft)
            <div class="mb-6 p-4 bg-gradient-to-r from-blue-500/20 to-blue-600/20 border border-blue-500/50 text-blue-200 rounded-xl flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <span>Continuing your draft. Make changes and save or submit when ready.</span>
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
                    Location
                </h3>
                @livewire('location-picker', ['locatableType' => $locatableType, 'locatableId' => $locatableId])
                @error('locatableType') <x-input-error :messages="$message" class="mt-2" /> @enderror
            </div>

            {{-- Category --}}
            <div>
                <x-input-label for="categoryId" class="mb-2">Category <span class="text-red-400">*</span></x-input-label>
                <x-select wire:model="categoryId" id="categoryId">
                    <option value="">Select a category...</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </x-select>
                @error('categoryId') <x-input-error :messages="$message" class="mt-1" /> @enderror
            </div>

            {{-- Excerpt/Summary --}}
            <div>
                <x-input-label for="excerpt" class="mb-2">Summary <span class="text-red-400">*</span></x-input-label>
                <textarea wire:model="excerpt" id="excerpt" rows="3"
                    placeholder="A brief summary of your guide that will appear in search results and previews..."
                    class="border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg shadow-inner p-2.5 px-4 text-base w-full transition-colors duration-200"></textarea>
                <p class="mt-1 text-sm text-steel-500">50-500 characters</p>
                @error('excerpt') <x-input-error :messages="$message" class="mt-1" /> @enderror
            </div>

            {{-- Body Content --}}
            <div>
                <x-input-label class="mb-2">Guide Content <span class="text-red-400">*</span></x-input-label>
                <div wire:ignore>
                    <x-wysiwyg id="body" name="body" data-initial-content="{{ $body }}"/>
                </div>
                @error('body') <x-input-error :messages="$message" class="mt-1" /> @enderror
            </div>

            {{-- Images Section --}}
            <div class="bg-steel-900/50 rounded-xl p-4 border border-steel-700/50 space-y-6">
                <h3 class="font-semibold text-steel-200 flex items-center gap-2">
                    <svg class="w-5 h-5 text-accent-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Images
                </h3>

                {{-- Featured Image --}}
                <div>
                    <x-input-label class="mb-2">Featured Image</x-input-label>

                    {{-- Existing Featured Image (from saved draft) --}}
                    @if($existingFeaturedImage)
                        <div class="relative inline-block mb-3">
                            <img src="{{ Storage::url($existingFeaturedImage) }}" class="h-32 w-auto rounded-lg border border-steel-700" alt="Featured image">
                            <button type="button" wire:click="removeExistingFeaturedImage"
                                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    @elseif($featuredImage)
                        <div class="relative inline-block mb-3">
                            <img src="{{ $featuredImage->temporaryUrl() }}" class="h-32 w-auto rounded-lg border border-steel-700" alt="Preview">
                            <button type="button" wire:click="removeFeaturedImage"
                                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    @endif

                    @if(!$existingFeaturedImage)
                        <input type="file" wire:model="featuredImage" accept="image/*"
                            class="block w-full text-sm text-steel-400
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-lg file:border-0
                                file:text-sm file:font-semibold
                                file:bg-accent-500 file:text-white
                                hover:file:bg-accent-600 file:cursor-pointer
                                file:transition-colors">

                        <p class="mt-1 text-sm text-steel-500">Maximum 5MB. JPG, PNG, or GIF.</p>
                    @endif
                    @error('featuredImage') <x-input-error :messages="$message" class="mt-1" /> @enderror
                </div>

                {{-- Gallery Images --}}
                <div>
                    <x-input-label class="mb-2">Gallery Images <span class="text-steel-500">(optional)</span></x-input-label>

                    {{-- Existing Gallery Images --}}
                    @if(count($existingGallery) > 0 || count($gallery) > 0)
                        <div class="flex flex-wrap gap-3 mb-3">
                            @foreach($existingGallery as $index => $imagePath)
                                <div class="relative">
                                    <img src="{{ Storage::url($imagePath) }}" class="h-24 w-auto rounded-lg border border-steel-700" alt="Gallery image">
                                    <button type="button" wire:click="removeExistingGalleryImage({{ $index }})"
                                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition-colors">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                            @foreach($gallery as $index => $image)
                                <div class="relative">
                                    <img src="{{ $image->temporaryUrl() }}" class="h-24 w-auto rounded-lg border border-steel-700" alt="Gallery preview">
                                    <button type="button" wire:click="removeGalleryImage({{ $index }})"
                                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition-colors">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <input type="file" wire:model="gallery" accept="image/*" multiple
                        class="block w-full text-sm text-steel-400
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-lg file:border-0
                            file:text-sm file:font-semibold
                            file:bg-steel-700 file:text-steel-200
                            hover:file:bg-steel-600 file:cursor-pointer
                            file:transition-colors">

                    <p class="mt-1 text-sm text-steel-500">Add up to 10 images. Each max 5MB.</p>
                    @error('gallery.*') <x-input-error :messages="$message" class="mt-1" /> @enderror
                </div>
            </div>

            {{-- List Builder Section --}}
            <div class="bg-steel-900/50 rounded-xl p-4 border border-steel-700/50">
                <div class="flex items-center justify-between mb-4">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" wire:model.live="listEnabled"
                            class="w-5 h-5 rounded border-steel-600 bg-steel-800 text-accent-500 focus:ring-accent-500/20 focus:ring-offset-0">
                        <span class="font-semibold text-steel-200 flex items-center gap-2">
                            <svg class="w-5 h-5 text-accent-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                            Include a List
                        </span>
                    </label>

                    @if($listEnabled)
                        <label class="flex items-center gap-2 cursor-pointer text-sm">
                            <input type="checkbox" wire:model.live="listIsRanked"
                                class="w-4 h-4 rounded border-steel-600 bg-steel-800 text-accent-500 focus:ring-accent-500/20 focus:ring-offset-0">
                            <span class="text-steel-300">Ranked list</span>
                        </label>
                    @endif
                </div>

                @if($listEnabled)
                    <p class="text-sm text-steel-400 mb-4">
                        Create a list of items (e.g., "Top 5 Restaurants"). Drag to reorder.
                    </p>

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
                                    $wire.reorderListItems(items);
                                }
                            });
                        ">
                        @foreach($listItems as $index => $item)
                            <div wire:key="list-item-{{ $item['id'] }}" data-item-id="{{ $item['id'] }}"
                                class="bg-steel-800/50 rounded-lg border border-steel-700/50 overflow-hidden">
                                {{-- Item Header --}}
                                <div class="flex items-center gap-3 p-3 cursor-pointer" wire:click="toggleListItem({{ $index }})">
                                    {{-- Drag Handle --}}
                                    <div class="drag-handle cursor-grab text-steel-500 hover:text-steel-300">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M7 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/>
                                        </svg>
                                    </div>

                                    {{-- Rank Number --}}
                                    @if($listIsRanked)
                                        <span class="flex items-center justify-center w-8 h-8 rounded-full bg-accent-500/20 text-accent-400 font-bold text-sm">
                                            #{{ $index + 1 }}
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
                                    <button type="button" wire:click.stop="removeListItem({{ $index }})"
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
                                            <input type="text" wire:model="listItems.{{ $index }}.title"
                                                placeholder="e.g., Best Pizza Place"
                                                class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg p-2.5 text-sm">
                                            @error("listItems.{$index}.title") <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                                        </div>

                                        {{-- Description --}}
                                        <div>
                                            <label class="block text-sm font-medium text-steel-300 mb-1">Description <span class="text-red-400">*</span></label>
                                            <textarea wire:model="listItems.{{ $index }}.description" rows="3"
                                                placeholder="Describe why this made the list..."
                                                class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg p-2.5 text-sm"></textarea>
                                            @error("listItems.{$index}.description") <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            {{-- Image --}}
                                            <div>
                                                <label class="block text-sm font-medium text-steel-300 mb-1">Image</label>
                                                @if($item['image'])
                                                    <div class="relative inline-block mb-2">
                                                        <img src="{{ Storage::url($item['image']) }}" class="h-20 w-auto rounded-lg border border-steel-700" alt="Item image">
                                                        <button type="button" wire:click="removeListItemImage({{ $index }})"
                                                            class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition-colors">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                @elseif(isset($listItemImages[$item['id']]) && $listItemImages[$item['id']])
                                                    <div class="relative inline-block mb-2">
                                                        <img src="{{ $listItemImages[$item['id']]->temporaryUrl() }}" class="h-20 w-auto rounded-lg border border-steel-700" alt="Preview">
                                                        <button type="button" wire:click="removeListItemImage({{ $index }})"
                                                            class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 transition-colors">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                @else
                                                    <input type="file" wire:model="listItemImages.{{ $item['id'] }}" accept="image/*"
                                                        class="block w-full text-xs text-steel-400
                                                            file:mr-2 file:py-1.5 file:px-3
                                                            file:rounded-lg file:border-0
                                                            file:text-xs file:font-medium
                                                            file:bg-steel-700 file:text-steel-200
                                                            hover:file:bg-steel-600 file:cursor-pointer">
                                                @endif
                                            </div>

                                            {{-- Address/Link --}}
                                            <div>
                                                <label class="block text-sm font-medium text-steel-300 mb-1">Address or Link</label>
                                                <input type="text" wire:model="listItems.{{ $index }}.address"
                                                    placeholder="123 Main St or https://..."
                                                    class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg p-2.5 text-sm">
                                            </div>
                                        </div>

                                        {{-- Rating --}}
                                        <div>
                                            <label class="block text-sm font-medium text-steel-300 mb-2">Rating</label>
                                            <div class="flex items-center gap-1">
                                                @for($star = 1; $star <= 5; $star++)
                                                    <button type="button" wire:click="setListItemRating({{ $index }}, {{ ($item['rating'] ?? 0) === $star ? 'null' : $star }})"
                                                        class="text-2xl transition-colors {{ ($item['rating'] ?? 0) >= $star ? 'text-amber-400' : 'text-steel-600 hover:text-steel-500' }}">
                                                        ★
                                                    </button>
                                                @endfor
                                                @if($item['rating'])
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
                    <button type="button" wire:click="addListItem"
                        class="mt-4 w-full py-3 border-2 border-dashed border-steel-600 rounded-lg text-steel-400 hover:text-steel-300 hover:border-steel-500 transition-colors flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Item
                    </button>
                @endif
            </div>

            {{-- Submit / Save Draft --}}
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pt-4 border-t border-steel-700/50"
                x-data="{
                    syncAndSave() {
                        if (typeof $ !== 'undefined' && $('#body').data('trumbowyg')) {
                            $wire.set('body', $('#body').trumbowyg('html'));
                        }
                        $wire.saveDraft();
                    },
                    syncAndSubmit() {
                        if (typeof $ !== 'undefined' && $('#body').data('trumbowyg')) {
                            $wire.set('body', $('#body').trumbowyg('html'));
                        }
                        $wire.submit();
                    }
                }">
                <div class="flex items-center gap-3">
                    <button type="button" @click="syncAndSave()" wire:loading.attr="disabled" wire:target="saveDraft,featuredImage,gallery"
                        class="inline-flex items-center px-4 py-2 bg-steel-700 text-steel-200 rounded-lg hover:bg-steel-600 transition-colors font-semibold disabled:opacity-50">
                        <span wire:loading.remove wire:target="saveDraft">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                            </svg>
                            Save Draft
                        </span>
                        <span wire:loading wire:target="saveDraft" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            Saving...
                        </span>
                    </button>
                    <span class="text-sm text-steel-500">Save and continue later</span>
                </div>

                <button type="button" @click="syncAndSubmit()" wire:loading.attr="disabled" wire:target="submit,featuredImage,gallery"
                    class="inline-flex items-center px-4 py-2 bg-accent-500 text-white rounded-lg hover:bg-accent-600 transition-colors font-semibold disabled:opacity-50 shadow-lg shadow-accent-500/20">
                    <span wire:loading.remove wire:target="submit">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Submit for Review
                    </span>
                    <span wire:loading wire:target="submit" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        Submitting...
                    </span>
                </button>
            </div>
        </form>
    @endif
</div>

@assets
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
@endassets

@script
<script>
    // Sync WYSIWYG content to Livewire
    const editor = document.querySelector('#body');
    if (editor) {
        // Wait for trumbowyg to be initialized, then set initial content
        const waitForTrumbowyg = setInterval(() => {
            if ($('#body').data('trumbowyg')) {
                clearInterval(waitForTrumbowyg);

                // Load initial content if editing a draft
                const initialContent = editor.dataset.initialContent;
                if (initialContent) {
                    $('#body').trumbowyg('html', initialContent);
                }
            }
        }, 100);

        const syncContent = () => {
            const content = $('#body').trumbowyg('html');
            $wire.set('body', content);
        };

        // Sync on change
        $('#body').on('tbwchange', syncContent);

        // Also sync before form submit and save draft
        $wire.on('submit', () => {
            syncContent();
        });

        $wire.on('saveDraft', () => {
            syncContent();
        });
    }
</script>
@endscript
