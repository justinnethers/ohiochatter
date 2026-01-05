<div>
    @if($pickem)
        <div class="bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl border border-steel-700/50 p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-white flex items-center gap-2">
                    <span class="w-1 h-4 bg-accent-500 rounded-full"></span>
                    Active Pick 'Em
                </h3>
                @if($pickem->picks_lock_at)
                    <span class="text-xs text-steel-500">Locks in {{ $pickem->picks_lock_at->diffForHumans(null, true) }}</span>
                @endif
            </div>

            <a href="{{ route('pickem.show', $pickem) }}" class="block text-steel-200 hover:text-white text-sm mb-3">
                {{ $pickem->title }}
            </a>

            @if($pickem->group)
                <a href="{{ route('pickem.group', $pickem->group) }}" class="inline-flex items-center px-2 py-0.5 bg-accent-500 rounded-full text-xs font-semibold text-white mb-3">
                    {{ $pickem->group->name }}
                </a>
            @endif

            <div class="flex flex-wrap items-center gap-2 mb-3">
                <div class="inline-flex items-center gap-1 px-2 py-0.5 bg-steel-900/70 rounded-full border border-steel-700/50">
                    <span class="font-bold text-steel-100 text-xs">{{ $pickem->matchups->count() }}</span>
                    <span class="text-xs text-steel-400">{{ Str::plural('matchup', $pickem->matchups->count()) }}</span>
                </div>
                @if($this->participantCount > 0)
                    <div class="inline-flex items-center gap-1 px-2 py-0.5 bg-steel-900/70 rounded-full border border-steel-700/50">
                        <svg class="w-3 h-3 text-steel-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                        </svg>
                        <span class="font-bold text-steel-100 text-xs">{{ $this->participantCount }}</span>
                    </div>
                @endif
            </div>

            <a href="{{ route('pickem.show', $pickem) }}" class="flex items-center justify-between text-xs">
                @auth
                    @if($hasSubmitted)
                        <span class="text-emerald-400">Submitted</span>
                    @else
                        <span class="text-steel-400">Not yet submitted</span>
                    @endif
                    <span class="text-accent-400 hover:text-accent-300">{{ $hasSubmitted ? 'View picks' : 'Make picks' }} &rarr;</span>
                @else
                    <span class="text-steel-400">Login to participate</span>
                    <span class="text-accent-400 hover:text-accent-300">View &rarr;</span>
                @endauth
            </a>
        </div>
    @endif
</div>
