<div>
    @if($isAdmin)
        <div class="bg-gradient-to-br from-steel-800 to-steel-850 p-3 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50 mb-4">
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-steel-400 text-sm font-medium">Admin:</span>

                <!-- Move Thread -->
                <div class="flex items-center gap-2">
                    <label for="forum-select" class="text-steel-300 text-sm">Move to:</label>
                    <select
                        id="forum-select"
                        wire:model="selectedForumId"
                        class="bg-steel-700 text-steel-200 text-sm rounded-lg border border-steel-600 px-3 py-1.5 focus:ring-accent-500 focus:border-accent-500"
                    >
                        <option value="">Select Forum</option>
                        @foreach($forums as $forum)
                            <option value="{{ $forum->id }}">{{ $forum->name }}</option>
                        @endforeach
                    </select>
                    <button
                        wire:click="moveThread"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-3 py-1.5 rounded-lg font-semibold text-sm bg-gradient-to-r from-accent-500 to-accent-600 text-white shadow-lg shadow-accent-500/25 hover:shadow-accent-500/40 hover:from-accent-600 hover:to-accent-700 transition-all duration-200 hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="moveThread">Move</span>
                        <span wire:loading wire:target="moveThread">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </button>
                    @error('selectedForumId')
                        <span class="text-red-400 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <div class="h-6 w-px bg-steel-600 hidden sm:block"></div>

                <!-- Delete Thread -->
                <button
                    wire:click="deleteThread"
                    wire:confirm="Are you sure you want to delete this thread? This action cannot be undone."
                    wire:loading.attr="disabled"
                    class="inline-flex items-center px-3 py-1.5 rounded-lg font-semibold text-sm bg-gradient-to-r from-red-500 to-red-600 text-white shadow-lg shadow-red-500/25 hover:shadow-red-500/40 hover:from-red-600 hover:to-red-700 transition-all duration-200 hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50"
                >
                    <span wire:loading.remove wire:target="deleteThread" class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Delete
                    </span>
                    <span wire:loading wire:target="deleteThread">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                </button>

                <div class="h-6 w-px bg-steel-600 hidden sm:block"></div>

                <!-- Lock/Unlock Thread -->
                <button
                    wire:click="toggleLock"
                    wire:confirm="{{ $isLocked ? 'Are you sure you want to unlock this thread?' : 'Are you sure you want to lock this thread?' }}"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center px-3 py-1.5 rounded-lg font-semibold text-sm shadow-lg transition-all duration-200 hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50 {{ $isLocked ? 'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white shadow-emerald-500/25 hover:shadow-emerald-500/40 hover:from-emerald-600 hover:to-emerald-700' : 'bg-gradient-to-r from-amber-500 to-amber-600 text-white shadow-amber-500/25 hover:shadow-amber-500/40 hover:from-amber-600 hover:to-amber-700' }}"
                >
                    <span wire:loading.remove wire:target="toggleLock" class="flex items-center">
                        @if($isLocked)
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                            </svg>
                            Unlock
                        @else
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            Lock
                        @endif
                    </span>
                    <span wire:loading wire:target="toggleLock">
                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                </button>
            </div>
        </div>
    @endif
</div>
