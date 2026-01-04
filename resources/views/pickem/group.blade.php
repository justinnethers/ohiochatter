<x-app-layout>
    <x-slot name="title">{{ $group->name }} - Pick 'Ems</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('pickem.index') }}" class="text-steel-400 hover:text-white transition-colors shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <h2 class="text-lg md:text-xl font-bold text-white leading-tight truncate">
                    {{ $group->name }}
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="container mx-auto">
        <div class="grid md:grid-cols-3 gap-6 md:mt-4">
            {{-- Main Content - Pickems List --}}
            <div class="md:col-span-2">
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
                                <a href="{{ route('pickem.show', $pickem) }}"
                                   class="block bg-gradient-to-br from-steel-800 to-steel-850 p-4 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50 hover:border-accent-500/50 transition-all duration-200 group">
                                    <div class="flex items-center justify-between gap-4">
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-semibold text-white group-hover:text-accent-400 transition-colors truncate">
                                                {{ $pickem->title }}
                                            </h3>
                                            <div class="flex items-center gap-3 mt-1 text-sm text-steel-400">
                                                <span>{{ $pickem->matchups->count() }} matchups</span>
                                                <span class="capitalize">{{ $pickem->scoring_type }}</span>
                                            </div>
                                        </div>
                                        @if($pickem->isLocked())
                                            <span class="px-2 py-1 bg-red-500/20 text-red-400 rounded text-xs font-medium">Locked</span>
                                        @elseif($pickem->picks_lock_at)
                                            <span class="px-2 py-1 bg-green-500/20 text-green-400 rounded text-xs font-medium">Open</span>
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
            <div class="md:col-span-1">
                <div class="md:rounded-2xl bg-gradient-to-br from-steel-800/50 to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-4 md:p-6 sticky top-24">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-accent-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                        </svg>
                        Leaderboard
                    </h3>

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
