<div
    x-data="{
        open: false,
    }"
    @search-updated.window="$refs.searchInput.value = ''"
    @click.away="open = false"
    class="relative"
>
    <div>
        <x-input-label for="subject">Recipients</x-input-label>
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
            class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-md bg-gray-800 py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm"
        >
            @foreach($filteredUsers as $user)
                <button
                    type="button"
                    wire:click="selectUser({{ $user->id }})"
                    class="text-left flex gap-2 relative w-full cursor-pointer select-none py-2 px-3 text-gray-100 hover:bg-gray-700"
                >
                    <x-avatar :avatar-path="$user->avatar_path" :size="6"/>
                    {{ $user->username }}
                </button>
            @endforeach
        </div>
    @elseif($search !== '' && strlen($search) >= 2)
        <div
            x-show="open"
            class="absolute z-10 mt-1 w-full rounded-md bg-gray-800 py-1 shadow-lg ring-1 ring-black ring-opacity-5"
        >
            <div class="py-2 px-3 text-sm text-gray-400">
                No users found
            </div>
        </div>
    @endif

    <!-- Newly selected users pills -->
    @if($selectedUsers->count() > 0)
        <div class="mt-2 flex flex-wrap gap-2">
            @foreach($selectedUsers as $user)
                <span class="inline-flex items-center gap-1 rounded-full bg-blue-400/10 px-2 py-1 text-xs font-semibold text-blue-400">
                    <x-avatar :avatar-path="$user->avatar_path" :size="6"/>
                    {{ $user->username }}
                    <button
                        type="button"
                        wire:click="removeUser({{ $user->id }})"
                        class="text-blue-400/50 hover:text-blue-400"
                    >
                        <span class="sr-only">Remove user {{ $user->username }}</span>
                        ×
                    </button>
                </span>
            @endforeach
        </div>
    @endif
</div>
