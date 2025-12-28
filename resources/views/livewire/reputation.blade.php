<div class="flex gap-3 items-center">
    {{-- Positive Rep --}}
    <div class="flex items-center gap-2">
        <button
            wire:click="rep"
            class="bg-gradient-to-br from-emerald-400 to-emerald-500 rounded-lg text-white h-8 w-8 flex items-center justify-center shadow-md shadow-emerald-500/25 hover:shadow-lg hover:shadow-emerald-500/40 hover:scale-105 active:scale-95 transition-all duration-150">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" class="fill-current" viewBox="0 0 24 24"><path d="M15.43 8.814c.808-3.283 1.252-8.814-2.197-8.814-1.861 0-2.35 1.668-2.833 3.329-1.971 6.788-5.314 7.342-8.4 7.743v9.928c3.503 0 5.584.729 8.169 1.842 1.257.541 3.053 1.158 5.336 1.158 2.538 0 4.295-.997 5.009-3.686.5-1.877 1.486-7.25 1.486-8.25 0-1.649-1.168-2.446-2.594-2.507-1.21-.051-2.87-.277-3.976-.743zm3.718 4.321l-1.394.167s-.609 1.109.141 1.115c0 0 .201.01 1.069-.027 1.082-.046 1.051 1.469.004 1.563l-1.761.099c-.734.094-.656 1.203.141 1.172 0 0 .686-.017 1.143-.041 1.068-.056 1.016 1.429.04 1.551-.424.053-1.745.115-1.745.115-.811.072-.706 1.235.109 1.141l.771-.031c.822-.074 1.003.825-.292 1.661-1.567.881-4.685.131-6.416-.614-2.238-.965-4.437-1.934-6.958-2.006v-6c3.263-.749 6.329-2.254 8.321-9.113.898-3.092 1.679-1.931 1.679.574 0 2.071-.49 3.786-.921 5.533 1.061.543 3.371 1.402 6.12 1.556 1.055.059 1.025 1.455-.051 1.585z"/></svg>
        </button>
        @if (count($post->reps) > 0)
            <div class="flex bg-gradient-to-r from-emerald-400 to-emerald-500 items-center gap-1 rounded-lg h-8 px-2 shadow-md shadow-emerald-500/25">
                @foreach ($post->reps as $index => $rep)
                    <div class="relative" x-data="{ show: false }" @mouseenter="show = true" @mouseleave="show = false">
                        <x-avatar size="5" :avatar-path="$rep->user->avatar_path" class="ring-2 ring-white/50 cursor-pointer hover:scale-110 hover:z-50 transition-transform" />
                        <div x-show="show" x-transition.opacity.duration.150ms class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-steel-900 text-white text-xs rounded shadow-lg pointer-events-none whitespace-nowrap z-50">
                            {{ $rep->user->username }}
                        </div>
                    </div>
                @endforeach
                <span class="text-white text-sm font-bold ml-1">{{ count($post->reps) }}</span>
            </div>
        @endif
    </div>

    {{-- Negative Rep --}}
    <div class="flex items-center gap-2">
        <button
            wire:click="neg"
            class="bg-gradient-to-br from-rose-400 to-rose-500 rounded-lg text-white h-8 w-8 flex items-center justify-center shadow-md shadow-rose-500/25 hover:shadow-lg hover:shadow-rose-500/40 hover:scale-105 active:scale-95 transition-all duration-150">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" class="fill-current" viewBox="0 0 24 24"><path d="M19.406 14.442c1.426-.06 2.594-.858 2.594-2.506 0-1-.986-6.373-1.486-8.25-.714-2.689-2.471-3.686-5.009-3.686-2.283 0-4.079.617-5.336 1.158-2.585 1.113-4.665 1.842-8.169 1.842v9.928c3.086.401 6.43.956 8.4 7.744.483 1.66.972 3.328 2.833 3.328 3.448 0 3.005-5.531 2.196-8.814 1.107-.466 2.767-.692 3.977-.744zm-.207-1.992c-2.749.154-5.06 1.013-6.12 1.556.431 1.747.921 3.462.921 5.533 0 2.505-.781 3.666-1.679.574-1.993-6.859-5.057-8.364-8.321-9.113v-6c2.521-.072 4.72-1.041 6.959-2.005 1.731-.745 4.849-1.495 6.416-.614 1.295.836 1.114 1.734.292 1.661l-.771-.032c-.815-.094-.92 1.068-.109 1.141 0 0 1.321.062 1.745.115.976.123 1.028 1.607-.04 1.551-.457-.024-1.143-.041-1.143-.041-.797-.031-.875 1.078-.141 1.172 0 0 .714.005 1.761.099s1.078 1.609-.004 1.563c-.868-.037-1.069-.027-1.069-.027-.75.005-.874 1.028-.141 1.115l1.394.167c1.075.13 1.105 1.526.05 1.585z"/></svg>
        </button>
        @if (count($post->negs) > 0)
            <div class="flex bg-gradient-to-r from-rose-400 to-rose-500 items-center gap-1 rounded-lg h-8 px-2 shadow-md shadow-rose-500/25">
                @foreach ($post->negs as $index => $neg)
                    <div class="relative" x-data="{ show: false }" @mouseenter="show = true" @mouseleave="show = false">
                        <x-avatar size="5" :avatar-path="$neg->user->avatar_path" class="ring-2 ring-white/50 cursor-pointer hover:scale-110 hover:z-50 transition-transform" />
                        <div x-show="show" x-transition.opacity.duration.150ms class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-steel-900 text-white text-xs rounded shadow-lg pointer-events-none whitespace-nowrap z-50">
                            {{ $neg->user->username }}
                        </div>
                    </div>
                @endforeach
                <span class="text-white text-sm font-bold ml-1">{{ count($post->negs) }}</span>
            </div>
        @endif
    </div>
</div>
