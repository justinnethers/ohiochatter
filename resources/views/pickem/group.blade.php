<x-app-layout>
    <x-slot name="title">{{ $group->name }} - Pick 'Ems</x-slot>
    <x-slot name="header">
        <h2 class="text-lg md:text-xl font-bold text-white leading-tight flex items-center gap-3 min-w-0">
            <span class="hidden md:inline-block w-1 h-6 bg-accent-500 rounded-full"></span>
            <a href="{{ route('pickem.index') }}" class="text-steel-400 hover:text-accent-400 transition-colors">Pick
                'Ems</a>
            <span class="text-steel-600">/</span>
            <span class="truncate">{{ $group->name }}</span>
        </h2>
    </x-slot>

    <div class="container mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:mt-4">
            {{-- Main Content - Pickems List --}}
            <div class="lg:col-span-2">
                <div
                    class="md:rounded-2xl md:bg-gradient-to-br md:from-steel-800/50 md:to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-2 md:p-6">
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
                                   class="group block bg-gradient-to-br from-steel-800 to-steel-850 p-3 md:p-4 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50 hover:border-steel-600 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 relative overflow-hidden">
                                    {{-- Left edge indicator --}}
                                    @if($isComplete)
                                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-steel-600/50"></div>
                                    @else
                                        <div
                                            class="absolute left-0 top-4 bottom-4 w-1 bg-accent-500 rounded-r-full"></div>
                                    @endif

                                    <h3 class="text-lg md:text-xl font-semibold text-white group-hover:text-accent-400 transition-colors duration-200 leading-snug mb-3">
                                        {{ $pickem->title }}
                                    </h3>

                                    {{-- Metadata row --}}
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <div class="flex flex-wrap items-center gap-3">
                                            <span
                                                class="inline-flex items-center px-3 py-1 bg-accent-500 rounded-full text-sm font-semibold text-white shadow-lg shadow-black/20">
                                                {{ $pickem->matchups->count() }} {{ Str::plural('matchup', $pickem->matchups->count()) }}
                                            </span>
                                            @if($participantCount > 0)
                                                <span
                                                    class="inline-flex items-center gap-2 px-3 py-1 bg-steel-900/70 rounded-full border border-steel-700/50">
                                                    <svg class="w-4 h-4 text-steel-400" fill="currentColor"
                                                         viewBox="0 0 20 20">
                                                        <path
                                                            d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                                                    </svg>
                                                    <span
                                                        class="font-bold text-steel-100">{{ $participantCount }}</span>
                                                </span>
                                            @endif
                                            <span class="text-sm text-steel-400">
                                                @if(!$isComplete && $pickem->picks_lock_at)
                                                    <span
                                                        class="text-emerald-400">Locks {{ $pickem->picks_lock_at->diffForHumans() }}</span>
                                                @elseif($isComplete)
                                                    Completed
                                                @endif
                                            </span>
                                        </div>

                                        {{-- Winner display for completed pickems --}}
                                        @if($winner && $isComplete)
                                            <div class="flex items-center gap-2">
                                                <img src="{{ $winner['user']->avatar_path }}" alt=""
                                                     class="w-6 h-6 rounded-full ring-2 ring-yellow-500/50">
                                                <span
                                                    class="text-yellow-400 font-medium">{{ $winner['user']->username }}</span>
                                                <span
                                                    class="text-sm text-steel-400">{{ $winner['score'] }}/{{ $winner['max'] }}</span>
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
                <div
                    class="md:rounded-2xl bg-gradient-to-br from-steel-800/50 to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-4 md:p-6 sticky top-24">
                    <h3 class="text-lg font-semibold text-white mb-4">SLeaderboard</h3>

                    @if($leaderboard->isEmpty())
                        <p class="text-steel-400 text-sm">No scores yet</p>
                    @else
                        <div class="space-y-1">
                            @foreach($leaderboard as $entry)
                                <div
                                    class="flex items-center gap-3 p-2 rounded-lg {{ $loop->index < 3 ? 'bg-steel-700/30' : '' }}">
                                    @if($loop->first)
                                        <span
                                            class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-yellow-500/20 text-yellow-400 font-bold text-sm">1</span>
                                    @elseif($loop->index == 1)
                                        <span
                                            class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-gray-400/20 text-gray-300 font-bold text-sm">2</span>
                                    @elseif($loop->index == 2)
                                        <span
                                            class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-600/20 text-amber-500 font-bold text-sm">3</span>
                                    @else
                                        <span
                                            class="inline-flex items-center justify-center w-7 h-7 text-white font-medium text-sm">{{ $loop->iteration }}</span>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <a href="{{ route('profile.show', $entry->username) }}"
                                           class="text-white hover:text-accent-400 truncate block">
                                            {{ $entry->username }}
                                        </a>
                                    </div>
                                    <span
                                        class="font-semibold text-accent-400">{{ (int) $entry->total_points }} pts</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
