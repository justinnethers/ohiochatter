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
                    <div class="flex flex-wrap gap-2">
                        @foreach($groups as $group)
                            <a href="{{ route('pickem.group', $group) }}"
                               class="inline-flex items-center px-3 py-1 bg-accent-500 rounded-full text-xs md:text-sm font-semibold text-white shadow-lg shadow-black/20 hover:bg-accent-600 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200">
                                {{ $group->name }}
                                <span class="ml-1.5 text-white/70">({{ $group->pickems_count }})</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($pickems->isEmpty())
                <div class="text-center py-12">
                    <p class="text-steel-400 text-lg">No Pick 'Ems yet</p>
                    <p class="text-steel-500 text-sm mt-1">Check back soon for upcoming games!</p>
                </div>
            @else
                <section>
                    @foreach($pickems as $pickem)
                        <article class="group bg-gradient-to-br from-steel-800 to-steel-850 border-steel-700/50 hover:border-steel-600 hover:shadow-xl hover:-translate-y-0.5 p-3 md:p-4 text-steel-100 rounded-xl mb-2 md:mb-4 shadow-lg shadow-black/20 border transition-all duration-300 relative overflow-hidden">
                            <a class="group/title text-lg md:text-xl font-semibold transition-colors duration-200 block leading-snug" href="{{ route('pickem.show', $pickem) }}">
                                <span class="text-white group-hover/title:text-accent-400 transition-colors duration-200">
                                    {{ $pickem->title }}
                                </span>
                            </a>


                            {{-- Bottom stats row --}}
                            <div class="flex flex-wrap text-sm mt-2 items-center gap-2">
                                @if($pickem->group)
                                    <a href="{{ route('pickem.group', $pickem->group) }}"
                                       class="inline-flex items-center px-2 md:px-3 py-0.5 md:py-1 bg-accent-500 rounded-full text-xs md:text-sm font-semibold text-white shadow-lg shadow-black/20 hover:bg-accent-600 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200">
                                        {{ $pickem->group->name }}
                                    </a>
                                @endif

                                <div class="inline-flex items-center gap-1 md:gap-2 px-2 md:px-3 py-0.5 md:py-1 bg-steel-900/70 rounded-full border border-steel-700/50">
                                    <span class="font-bold text-steel-100">{{ $pickem->matchups->count() }}</span>
                                    <span class="text-sm text-steel-400">{{ Str::plural('matchup', $pickem->matchups->count()) }}</span>
                                </div>

                                <div class="flex-1"></div>

                                <div class="flex items-center gap-2 text-sm">
                                    @auth
                                        @if($pickem->hasUserSubmitted(auth()->user()))
                                            <span class="text-emerald-400">Submitted</span>
                                            <span class="text-steel-600">&bull;</span>
                                        @endif
                                    @endauth
                                    @if($pickem->picks_lock_at)
                                        @if($pickem->isLocked())
                                            <span class="text-steel-500">Locked</span>
                                        @else
                                            <span class="text-steel-400">Locks {{ $pickem->picks_lock_at->diffForHumans() }}</span>
                                        @endif
                                    @else
                                        <span class="text-accent-400">Open</span>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @endforeach
                </section>

                <div class="mt-6">
                    {{ $pickems->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
