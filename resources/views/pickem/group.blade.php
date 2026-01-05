<x-app-layout>
    <x-slot name="title">{{ $group->name }} - Pick 'Ems</x-slot>
    <x-slot name="header">
        <h2 class="text-lg md:text-xl font-bold text-white leading-tight flex items-center gap-3 min-w-0">
            <span class="hidden md:inline-block w-1 h-6 bg-accent-500 rounded-full"></span>
            <a href="{{ route('pickem.index') }}" class="text-steel-400 hover:text-accent-400 transition-colors">Pick 'Ems</a>
            <span class="text-steel-600">/</span>
            <span class="truncate">{{ $group->name }}</span>
        </h2>
    </x-slot>

    <div class="container mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:mt-4">
            {{-- Main Content - Pickems List --}}
            <div class="lg:col-span-2">
                <div class="md:rounded-2xl md:bg-gradient-to-br md:from-steel-800/50 md:to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-2 md:p-6">
                    @if($group->description)
                        <p class="text-steel-300 mb-6">{{ $group->description }}</p>
                    @endif

                    @if($pickems->isEmpty())
                        <div class="text-center py-12">
                            <p class="text-steel-400">No Pick 'Ems in this group yet</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($pickems as $pickem)
                                @php
                                    $participantCount = $pickem->getParticipantCount();
                                    $winner = $pickem->getWinner();
                                    $isComplete = $pickem->isLocked() || $pickem->is_finalized;
                                @endphp
                                <a href="{{ route('pickem.show', $pickem) }}"
                                   class="block bg-gradient-to-br from-steel-800 to-steel-850 p-4 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50 hover:border-accent-500/50 transition-all duration-200 group">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-semibold text-white group-hover:text-accent-400 transition-colors truncate">
                                                {{ $pickem->title }}
                                            </h3>
                                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-2 text-sm">
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-steel-900/50 rounded-full border border-steel-700/50">
                                                    <span class="font-medium text-steel-100">{{ $pickem->matchups->count() }}</span>
                                                    <span class="text-steel-400">{{ Str::plural('matchup', $pickem->matchups->count()) }}</span>
                                                </span>
                                                @if($participantCount > 0)
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-steel-900/50 rounded-full border border-steel-700/50">
                                                        <span class="font-medium text-steel-100">{{ $participantCount }}</span>
                                                        <span class="text-steel-400">{{ Str::plural('player', $participantCount) }}</span>
                                                    </span>
                                                @endif
                                                @if(!$isComplete && $pickem->picks_lock_at)
                                                    <span class="text-emerald-400">Locks {{ $pickem->picks_lock_at->diffForHumans() }}</span>
                                                @elseif($isComplete)
                                                    <span class="text-steel-500">Completed</span>
                                                @endif
                                            </div>
                                        </div>
                                        @if($winner && $isComplete)
                                            <div class="text-right shrink-0">
                                                <span class="text-xs text-steel-500 uppercase tracking-wide">Winner</span>
                                                <div class="flex items-center gap-2 mt-1">
                                                    <img src="{{ $winner['user']->avatar_path }}" alt="" class="w-6 h-6 rounded-full">
                                                    <span class="text-yellow-400 font-medium">{{ $winner['user']->username }}</span>
                                                </div>
                                                <div class="text-sm text-steel-400 mt-0.5">{{ $winner['score'] }}/{{ $winner['max'] }} pts</div>
                                            </div>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $pickems->links() }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Sidebar - Leaderboard --}}
            <div class="lg:col-span-1">
                <div class="md:rounded-2xl bg-gradient-to-br from-steel-800/50 to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-4 md:p-6 sticky top-24">
                    <h3 class="text-lg font-semibold text-white mb-4">Leaderboard</h3>

                    @if($leaderboard->isEmpty())
                        <p class="text-steel-400 text-sm">No scores yet</p>
                    @else
                        <div class="space-y-2">
                            @foreach($leaderboard as $index => $entry)
                                <div class="flex items-center gap-3 p-2 rounded-lg {{ $index < 3 ? 'bg-steel-700/30' : '' }}">
                                    <span class="w-6 text-center font-bold {{ $index === 0 ? 'text-yellow-400' : ($index === 1 ? 'text-gray-300' : ($index === 2 ? 'text-amber-600' : 'text-steel-400')) }}">
                                        {{ $index + 1 }}
                                    </span>
                                    <div class="flex-1 min-w-0">
                                        <a href="{{ route('profile.show', $entry->username) }}" class="text-white hover:text-accent-400 truncate block">
                                            {{ $entry->username }}
                                        </a>
                                    </div>
                                    <span class="font-semibold text-accent-400">{{ (int) $entry->total_points }} pts</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
