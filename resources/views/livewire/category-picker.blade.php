<div class="space-y-3">
    {{-- Parent categories as expandable sections --}}
    @foreach($this->parentCategories as $parent)
        <div class="border border-steel-700/50 rounded-lg overflow-hidden">
            {{-- Parent header (clickable to expand) --}}
            <button
                type="button"
                wire:click="toggleParent({{ $parent->id }})"
                class="w-full flex items-center justify-between p-3 bg-steel-800/50 hover:bg-steel-700/50 transition-colors"
            >
                <span class="font-medium text-white">{{ $parent->name }}</span>
                <svg
                    class="w-5 h-5 text-steel-400 transition-transform duration-200 {{ in_array($parent->id, $expandedParents) ? 'rotate-180' : '' }}"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            {{-- Children (collapsible) --}}
            @if(in_array($parent->id, $expandedParents))
                <div class="p-3 space-y-2 bg-steel-900/50 border-t border-steel-700/30">
                    @foreach($parent->children as $child)
                        <label class="flex items-center gap-3 p-2 rounded-lg cursor-pointer hover:bg-steel-800/50 transition-colors">
                            <input
                                type="checkbox"
                                wire:click="toggleCategory({{ $child->id }})"
                                @checked(in_array($child->id, $selectedCategoryIds))
                                class="w-4 h-4 rounded border-steel-600 bg-steel-900 text-accent-500 focus:ring-accent-500 focus:ring-offset-0 focus:ring-2"
                            />
                            <span class="text-steel-200">{{ $child->name }}</span>
                        </label>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach

    {{-- Selected categories as pills --}}
    @if(count($this->selectedCategories) > 0)
        <div class="pt-2">
            <p class="text-xs text-steel-500 mb-2">Selected categories:</p>
            <div class="flex flex-wrap gap-2">
                @foreach($this->selectedCategories as $category)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-accent-500/10 border border-accent-500/20 px-3 py-1.5 text-sm text-accent-400">
                        @if($category->parent)
                            <span class="text-steel-500">{{ $category->parent->name }} &rsaquo;</span>
                        @endif
                        {{ $category->name }}
                        <button
                            type="button"
                            wire:click="toggleCategory({{ $category->id }})"
                            class="ml-1 text-accent-400/70 hover:text-accent-300 transition-colors"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </span>
                @endforeach
            </div>
        </div>
    @endif
</div>
