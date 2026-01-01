@props(['block', 'index' => 0, 'path' => '', 'nested' => false])

<textarea wire:model="blocks.{{ $path }}.data.content"
    rows="{{ $nested ? 4 : 6 }}"
    placeholder="Enter {{ $nested ? 'additional ' : '' }}text content..."
    class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg p-2.5 text-sm"></textarea>
