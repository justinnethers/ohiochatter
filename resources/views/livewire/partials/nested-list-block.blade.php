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
    </div>

    {{-- Nested List Items --}}
    @if(!empty($nestedBlock['data']['items']))
        <div class="space-y-2 pl-2 border-l-2 border-steel-700/50">
            @foreach($nestedBlock['data']['items'] as $nestedItemIndex => $nestedItem)
                <div class="bg-steel-800/30 rounded-lg p-3 space-y-2">
                    <div class="flex items-start gap-2">
                        @if($nestedBlock['data']['ranked'] ?? true)
                            <span class="shrink-0 w-6 h-6 rounded-full bg-green-500/20 text-green-400 text-xs font-bold flex items-center justify-center">
                                {{ $nestedItemIndex + 1 }}
                            </span>
                        @endif
                        <div class="flex-1 space-y-2">
                            <input type="text" wire:model="blocks.{{ $parentIndex }}.data.items.{{ $itemIndex }}.blocks.{{ $nestedIndex }}.data.items.{{ $nestedItemIndex }}.title"
                                placeholder="Item title"
                                class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg p-2 text-sm">
                            <textarea wire:model="blocks.{{ $parentIndex }}.data.items.{{ $itemIndex }}.blocks.{{ $nestedIndex }}.data.items.{{ $nestedItemIndex }}.description"
                                placeholder="Description"
                                rows="2"
                                class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg p-2 text-sm"></textarea>
                        </div>
                        <button type="button" wire:click="removeNestedListItem({{ $parentIndex }}, {{ $itemIndex }}, {{ $nestedIndex }}, {{ $nestedItemIndex }})"
                            class="shrink-0 p-1 text-red-400 hover:text-red-300 hover:bg-red-500/20 rounded transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
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
