<div>
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-500/20 border border-green-500/50 rounded-lg text-green-400">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-500/20 border border-red-500/50 rounded-lg text-red-400">
            {{ session('error') }}
        </div>
    @endif

    {{-- Header with Add Button --}}
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-semibold text-white">Pick 'Em Groups</h3>
        <button
            wire:click="openForm"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gradient-to-r from-accent-500 to-accent-600 rounded-lg text-white font-semibold text-sm shadow-lg shadow-accent-500/25 hover:shadow-accent-500/40 transition-all duration-200"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            New Group
        </button>
    </div>

    {{-- Form Modal --}}
    @if($showForm)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:click.self="closeForm">
            <div class="bg-steel-800 rounded-xl p-6 w-full max-w-md border border-steel-700">
                <h4 class="text-lg font-semibold text-white mb-4">
                    {{ $editingId ? 'Edit Group' : 'Create Group' }}
                </h4>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="block font-semibold text-steel-200 mb-2">Name</label>
                        <input
                            type="text"
                            wire:model="name"
                            class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg shadow-inner p-2.5 px-4 text-base transition-colors duration-200"
                            placeholder="e.g., NFL 2024 Season"
                        >
                        @error('name')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block font-semibold text-steel-200 mb-2">Description (optional)</label>
                        <textarea
                            wire:model="description"
                            rows="3"
                            class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg shadow-inner p-2.5 px-4 text-base transition-colors duration-200 resize-none"
                            placeholder="Brief description of this group..."
                        ></textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button
                            type="button"
                            wire:click="closeForm"
                            class="px-4 py-2 bg-steel-700 hover:bg-steel-600 rounded-lg text-steel-200 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-gradient-to-r from-accent-500 to-accent-600 rounded-lg text-white font-semibold shadow-lg shadow-accent-500/25 hover:shadow-accent-500/40 transition-all duration-200"
                        >
                            {{ $editingId ? 'Update' : 'Create' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Groups List --}}
    @if($groups->isEmpty())
        <div class="text-center py-12">
            <p class="text-steel-400">No groups yet. Create one to organize your Pick 'Ems!</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($groups as $group)
                <div class="bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl p-4 border border-steel-700/50 flex items-center justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <h4 class="font-semibold text-white">{{ $group->name }}</h4>
                        @if($group->description)
                            <p class="text-sm text-steel-400 mt-1">{{ $group->description }}</p>
                        @endif
                        <p class="text-xs text-steel-500 mt-1">{{ $group->pickems_count }} Pick 'Em(s)</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            wire:click="openForm({{ $group->id }})"
                            class="p-2 text-steel-400 hover:text-white transition-colors"
                            title="Edit"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        @if($group->pickems_count === 0)
                            <button
                                wire:click="delete({{ $group->id }})"
                                wire:confirm="Are you sure you want to delete this group?"
                                class="p-2 text-steel-400 hover:text-red-400 transition-colors"
                                title="Delete"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
