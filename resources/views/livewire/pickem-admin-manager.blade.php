<div>
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-500/20 border border-green-500/50 rounded-lg text-green-400">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="flex flex-col md:flex-row gap-4 mb-6">
        <div class="flex-1">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search Pick 'Ems..."
                class="w-full border border-steel-600 bg-steel-950 text-steel-100 placeholder-steel-500 focus:border-accent-500 focus:ring-2 focus:ring-accent-500/20 rounded-lg shadow-inner p-2.5 px-4 text-base transition-colors duration-200"
            >
        </div>
        <div class="flex gap-2">
            <button
                wire:click="$set('filterStatus', 'all')"
                @class([
                    'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                    'bg-accent-500 text-white' => $filterStatus === 'all',
                    'bg-steel-700 text-steel-300 hover:bg-steel-600' => $filterStatus !== 'all',
                ])
            >
                All
            </button>
            <button
                wire:click="$set('filterStatus', 'active')"
                @class([
                    'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                    'bg-accent-500 text-white' => $filterStatus === 'active',
                    'bg-steel-700 text-steel-300 hover:bg-steel-600' => $filterStatus !== 'active',
                ])
            >
                Active
            </button>
            <button
                wire:click="$set('filterStatus', 'locked')"
                @class([
                    'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                    'bg-accent-500 text-white' => $filterStatus === 'locked',
                    'bg-steel-700 text-steel-300 hover:bg-steel-600' => $filterStatus !== 'locked',
                ])
            >
                Locked
            </button>
            <button
                wire:click="$set('filterStatus', 'finalized')"
                @class([
                    'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                    'bg-accent-500 text-white' => $filterStatus === 'finalized',
                    'bg-steel-700 text-steel-300 hover:bg-steel-600' => $filterStatus !== 'finalized',
                ])
            >
                Finalized
            </button>
        </div>
    </div>

    {{-- Pick 'Ems List --}}
    @if($pickems->isEmpty())
        <div class="text-center py-12">
            <p class="text-steel-400">No Pick 'Ems found. Create one to get started!</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($pickems as $pickem)
                <div class="bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl p-4 border border-steel-700/50">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <h4 class="font-semibold text-white truncate">{{ $pickem->title }}</h4>
                                @if($pickem->is_finalized)
                                    <span class="px-2 py-0.5 bg-green-500/20 text-green-400 text-xs rounded-full">Finalized</span>
                                @elseif($pickem->isLocked())
                                    <span class="px-2 py-0.5 bg-yellow-500/20 text-yellow-400 text-xs rounded-full">Locked</span>
                                @else
                                    <span class="px-2 py-0.5 bg-blue-500/20 text-blue-400 text-xs rounded-full">Active</span>
                                @endif
                            </div>
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-steel-400">
                                @if($pickem->group)
                                    <span>Group: {{ $pickem->group->name }}</span>
                                @endif
                                <span>{{ $pickem->matchups_count }} matchup(s)</span>
                                <span>{{ $pickem->comments_count }} comment(s)</span>
                                <span>
                                    {{ ucfirst($pickem->scoring_type) }} scoring
                                </span>
                            </div>
                            @if($pickem->picks_lock_at)
                                <p class="text-xs text-steel-500 mt-1">
                                    Locks: {{ $pickem->picks_lock_at->format('M j, Y g:i A') }}
                                </p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <a
                                href="{{ route('pickem.show', $pickem) }}"
                                class="p-2 text-steel-400 hover:text-white transition-colors"
                                title="View"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </a>
                            <a
                                href="{{ route('pickem.admin.edit', $pickem) }}"
                                class="p-2 text-steel-400 hover:text-white transition-colors"
                                title="Edit"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                            <button
                                wire:click="delete({{ $pickem->id }})"
                                wire:confirm="Are you sure you want to delete '{{ $pickem->title }}'? This will also delete all matchups, picks, and comments."
                                class="p-2 text-steel-400 hover:text-red-400 transition-colors"
                                title="Delete"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $pickems->links() }}
        </div>
    @endif
</div>
