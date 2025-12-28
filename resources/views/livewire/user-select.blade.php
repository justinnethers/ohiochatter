<div
    x-data="{
        open: false,
    }"
    @search-updated.window="$refs.searchInput.value = ''"
    @click.away="open = false"
    class="relative"
>
    <div>
        <x-text-input
            x-ref="searchInput"
            wire:model.live.debounce.300ms="search"
            @focus="open = true"
            placeholder="Search users..."
            aria-labelledby="recipients-label"
        />
    </div>

    <!-- Hidden inputs -->
    @foreach($selectedUsers as $user)
        <input type="hidden" name="recipients[]" value="{{ $user->id }}">
    @endforeach

    <!-- Dropdown list -->
    @if(count($filteredUsers) > 0 && $search !== '')
        <div
            x-show="open"
            class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-lg bg-gradient-to-b from-steel-800 to-steel-850 py-1 shadow-xl shadow-black/30 border border-steel-700/50 focus:outline-none sm:text-sm"
        >
            @foreach($filteredUsers as $user)
                <button
                    type="button"
                    wire:click="selectUser({{ $user->id }})"
                    class="text-left flex gap-2 items-center relative w-full cursor-pointer select-none py-2.5 px-3 text-steel-100 hover:bg-steel-700/50 transition-colors"
                >
                    <x-avatar :avatar-path="$user->avatar_path" :size="6"/>
                    {{ $user->username }}
                </button>
            @endforeach
        </div>
    @elseif($search !== '' && strlen($search) >= 2)
        <div
            x-show="open"
            class="absolute z-10 mt-1 w-full rounded-lg bg-gradient-to-b from-steel-800 to-steel-850 py-1 shadow-xl shadow-black/30 border border-steel-700/50"
        >
            <div class="py-2 px-3 text-sm text-steel-400">
                No users found
            </div>
        </div>
    @endif

    <!-- Newly selected users pills -->
    @if($selectedUsers->count() > 0)
        <div class="mt-3 flex flex-wrap gap-2">
            @foreach($selectedUsers as $user)
                <span class="inline-flex items-center gap-2 rounded-full bg-accent-500/10 border border-accent-500/20 px-3 py-1.5 text-sm font-medium text-accent-300">
                    <x-avatar :avatar-path="$user->avatar_path" :size="5"/>
                    {{ $user->username }}
                    <button
                        type="button"
                        wire:click="removeUser({{ $user->id }})"
                        class="text-accent-400/50 hover:text-accent-300 transition-colors ml-1"
                    >
                        <span class="sr-only">Remove user {{ $user->username }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </span>
            @endforeach
        </div>
    @endif
</div>
