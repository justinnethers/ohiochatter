@php
    $colorMap = [
        'Food & Drink' => ['bg' => 'bg-amber-500', 'bg-light' => 'bg-amber-500/10', 'border' => 'border-amber-500/20', 'text' => 'text-amber-400', 'text-muted' => 'text-amber-500'],
        'Outdoors & Nature' => ['bg' => 'bg-emerald-500', 'bg-light' => 'bg-emerald-500/10', 'border' => 'border-emerald-500/20', 'text' => 'text-emerald-400', 'text-muted' => 'text-emerald-500'],
        'Arts & Culture' => ['bg' => 'bg-violet-500', 'bg-light' => 'bg-violet-500/10', 'border' => 'border-violet-500/20', 'text' => 'text-violet-400', 'text-muted' => 'text-violet-500'],
        'Entertainment' => ['bg' => 'bg-rose-500', 'bg-light' => 'bg-rose-500/10', 'border' => 'border-rose-500/20', 'text' => 'text-rose-400', 'text-muted' => 'text-rose-500'],
        'Shopping' => ['bg' => 'bg-sky-500', 'bg-light' => 'bg-sky-500/10', 'border' => 'border-sky-500/20', 'text' => 'text-sky-400', 'text-muted' => 'text-sky-500'],
        'Family' => ['bg' => 'bg-cyan-500', 'bg-light' => 'bg-cyan-500/10', 'border' => 'border-cyan-500/20', 'text' => 'text-cyan-400', 'text-muted' => 'text-cyan-500'],
    ];
@endphp

<div class="space-y-3">
    {{-- Horizontal tabs for parent categories --}}
    <div class="flex flex-wrap gap-1 border-b border-steel-700/50">
        @foreach($this->parentCategories as $parent)
            @php $colors = $colorMap[$parent->name] ?? ['bg' => 'bg-accent-500', 'text' => 'text-accent-400']; @endphp
            <button
                type="button"
                wire:click="setActiveTab({{ $parent->id }})"
                class="px-3 py-1.5 text-sm transition-colors -mb-px {{ $activeTab === $parent->id ? 'bg-steel-800 text-white font-medium border border-steel-700/50 border-b-steel-800 rounded-t-lg' : 'text-steel-400 hover:text-steel-200' }}"
            >
                {{ $parent->name }}
                @php
                    $selectedCount = collect($parent->children)->whereIn('id', $selectedCategoryIds)->count();
                @endphp
                @if($selectedCount > 0)
                    <span class="ml-1 inline-flex items-center justify-center w-5 h-5 text-xs rounded-full {{ $colors['bg'] }} text-white">{{ $selectedCount }}</span>
                @endif
            </button>
        @endforeach
    </div>

    {{-- Children of active tab --}}
    @foreach($this->parentCategories as $parent)
        @if($activeTab === $parent->id)
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                @foreach($parent->children as $child)
                    <label class="flex items-center gap-2 p-2 rounded-lg cursor-pointer hover:bg-steel-800/50 transition-colors border border-steel-700/30 {{ in_array($child->id, $selectedCategoryIds) ? 'bg-steel-800/50 border-accent-500/30' : '' }}">
                        <input
                            type="checkbox"
                            wire:click="toggleCategory({{ $child->id }})"
                            @checked(in_array($child->id, $selectedCategoryIds))
                            class="w-4 h-4 rounded border-steel-600 bg-steel-900 text-accent-500 focus:ring-accent-500 focus:ring-offset-0 focus:ring-2"
                        />
                        <span class="text-sm text-steel-200">{{ $child->name }}</span>
                    </label>
                @endforeach
            </div>
        @endif
    @endforeach

    {{-- Selected categories as pills --}}
    @if(count($this->selectedCategories) > 0)
        <div class="pt-2 border-t border-steel-700/30">
            <p class="text-xs text-steel-500 mb-2">Selected categories:</p>
            <div class="flex flex-wrap gap-2">
                @foreach($this->selectedCategories as $category)
                    @php $pillColors = $colorMap[$category->parent?->name] ?? ['bg-light' => 'bg-accent-500/10', 'border' => 'border-accent-500/20', 'text' => 'text-accent-400', 'text-muted' => 'text-accent-500']; @endphp
                    <span class="inline-flex items-center gap-1.5 rounded-full {{ $pillColors['bg-light'] }} border {{ $pillColors['border'] }} px-3 py-1.5 text-sm {{ $pillColors['text'] }}">
                        @if($category->parent)
                            <span class="{{ $pillColors['text-muted'] }}">{{ $category->parent->name }} &rsaquo;</span>
                        @endif
                        {{ $category->name }}
                        <button
                            type="button"
                            wire:click="toggleCategory({{ $category->id }})"
                            class="ml-1 opacity-70 hover:opacity-100 transition-opacity"
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
