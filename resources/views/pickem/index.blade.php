<x-app-layout>
    <x-slot name="title">Pick 'Ems</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-lg md:text-xl font-bold text-white leading-tight flex items-center gap-3">
                <span class="hidden md:inline-block w-1 h-6 bg-accent-500 rounded-full"></span>
                Pick 'Ems
            </h2>
            @if (auth()->check() && auth()->user()->is_admin)
                <a href="{{ route('pickem.admin.create') }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 md:px-4 md:py-2 bg-gradient-to-r from-accent-500 to-accent-600 rounded-lg text-white font-semibold text-xs md:text-sm shadow-lg shadow-accent-500/25 hover:shadow-accent-500/40 hover:from-accent-600 hover:to-accent-700 transition-all duration-200 whitespace-nowrap shrink-0">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    New Pick 'Em
                </a>
            @endif
        </div>
    </x-slot>

    <div class="container mx-auto">
        <div class="md:rounded-2xl md:bg-gradient-to-br md:from-steel-800/50 md:to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-2 md:p-8 md:pt-6 md:mt-4">

            @if($groups->isNotEmpty())
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-steel-400 uppercase tracking-wider mb-3">Groups</h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($groups as $group)
                            <a href="{{ route('pickem.group', $group) }}"
                               class="inline-flex items-center gap-2 px-3 py-1.5 bg-steel-700/50 hover:bg-steel-700 rounded-lg text-sm text-steel-200 transition-colors">
                                {{ $group->name }}
                                <span class="text-xs text-steel-400">({{ $group->pickems_count }})</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($pickems->isEmpty())
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-steel-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <p class="text-steel-400 text-lg">No Pick 'Ems yet</p>
                    <p class="text-steel-500 text-sm mt-1">Check back soon for upcoming games!</p>
                </div>
            @else
                <div class="space-y-4">
                    @foreach($pickems as $pickem)
                        <a href="{{ route('pickem.show', $pickem) }}"
                           class="block bg-gradient-to-br from-steel-800 to-steel-850 p-4 md:p-5 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50 hover:border-accent-500/50 transition-all duration-200 group">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-semibold text-white group-hover:text-accent-400 transition-colors truncate">
                                        {{ $pickem->title }}
                                    </h3>
                                    <div class="flex flex-wrap items-center gap-3 mt-2 text-sm text-steel-400">
                                        @if($pickem->group)
                                            <span class="inline-flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                                                </svg>
                                                {{ $pickem->group->name }}
                                            </span>
                                        @endif
                                        <span class="inline-flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                            </svg>
                                            {{ $pickem->matchups->count() }} matchups
                                        </span>
                                        <span class="inline-flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                            {{ $pickem->owner->username }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-2 shrink-0">
                                    @if($pickem->isLocked())
                                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-red-500/20 text-red-400 rounded text-xs font-medium">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                            </svg>
                                            Locked
                                        </span>
                                    @elseif($pickem->picks_lock_at)
                                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-500/20 text-green-400 rounded text-xs font-medium">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Locks {{ $pickem->picks_lock_at->diffForHumans() }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-accent-500/20 text-accent-400 rounded text-xs font-medium">
                                            Open
                                        </span>
                                    @endif
                                    <span class="text-xs text-steel-500 capitalize">{{ $pickem->scoring_type }}</span>
                                </div>
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
</x-app-layout>
