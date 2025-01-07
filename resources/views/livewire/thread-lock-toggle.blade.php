<div>
    @if($showButton)
        <button
            wire:click.prevent="toggleLock"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white {{ $isLocked ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700' }} focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            wire:loading.attr="disabled"
        >
            <span wire:loading.remove>
                {{ $isLocked ? 'Unlock Thread' : 'Lock Thread' }}
            </span>
            <span wire:loading>
                Processing...
            </span>
        </button>
    @endif
</div>
