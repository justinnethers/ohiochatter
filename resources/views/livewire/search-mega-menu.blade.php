<div
    x-data="{ open: false, showFilters: false }"
    @click.away="open = false"
    class="relative"
>
    {{-- Search Input --}}
    <div class="relative">
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            @focus="open = true"
            @input="open = true"
            placeholder="Search discussions, users, the archive..."
            class="w-full px-4 py-2.5 pl-10 bg-steel-950 border border-steel-600 rounded-lg text-white placeholder-steel-400 focus:outline-none focus:border-accent-500 focus:ring-1 focus:ring-accent-500"
        >
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-steel-500" fill="none" stroke="currentColor"
             viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>

    </div>

    {{-- Results Mega Menu --}}
    @if($search && strlen($search) >= 2)
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2"
            class="absolute z-[100] mt-2 w-full bg-gradient-to-b from-steel-800 to-steel-900 rounded-xl shadow-2xl shadow-black/40 border border-steel-700/50 overflow-hidden"
        >
            @if($this->hasResults())
                <div class="max-h-[70vh] overflow-y-auto p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {{-- Threads --}}
                        @if(isset($results['threads']) && $results['threads']->isNotEmpty())
                            <div>
                                <h4 class="text-xs font-semibold text-steel-400 uppercase tracking-wide mb-2 flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                    Threads
                                </h4>
                                <div class="space-y-1">
                                    @foreach($results['threads'] as $thread)
                                        <a href="{{ $thread->path() }}"
                                           class="block p-2 rounded-lg hover:bg-steel-700/50 transition-colors group">
                                            <div class="text-sm text-white group-hover:text-accent-300 font-medium line-clamp-1">{{ $thread->title }}</div>
                                            <div class="text-xs text-steel-500">{{ $thread->forum->name ?? 'Forum' }}</div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Users --}}
                        @if(isset($results['users']) && $results['users']->isNotEmpty())
                            <div>
                                <h4 class="text-xs font-semibold text-steel-400 uppercase tracking-wide mb-2 flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    Users
                                </h4>
                                <div class="space-y-1">
                                    @foreach($results['users'] as $user)
                                        <a href="{{ route('profile.show', $user) }}"
                                           class="flex items-center gap-2 p-2 rounded-lg hover:bg-steel-700/50 transition-colors group">
                                            <x-avatar :avatar-path="$user->avatar_path" :size="6"/>
                                            <span class="text-sm text-white group-hover:text-accent-300 font-medium">{{ $user->username }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Archive --}}
                        @if(isset($results['archive']) && $results['archive']->isNotEmpty())
                            <div>
                                <h4 class="text-xs font-semibold text-steel-400 uppercase tracking-wide mb-2 flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                    </svg>
                                    Archive
                                </h4>
                                <div class="space-y-1">
                                    @foreach($results['archive'] as $item)
                                        <a href="{{ route('archive.thread', $item['item']) }}"
                                           class="block p-2 rounded-lg hover:bg-steel-700/50 transition-colors group">
                                            <div class="text-sm text-white group-hover:text-accent-300 font-medium line-clamp-1">{{ $item['item']->title }}</div>
                                            <div class="text-xs text-steel-500">{{ $item['item']->forum?->title ?? 'Archive' }}</div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- View All Results Footer --}}
                <div class="border-t border-steel-700/50 p-3 bg-steel-900/50">
                    <a href="{{ route('search.show', ['q' => $search]) }}"
                       class="flex items-center justify-center gap-2 text-sm text-accent-400 hover:text-accent-300 font-medium transition-colors">
                        View all results for "{{ $search }}"
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </a>
                </div>
            @else
                {{-- No Results --}}
                <div class="p-6 text-center">
                    <svg class="w-10 h-10 mx-auto text-steel-600 mb-3" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <p class="text-steel-400 text-sm">No results found for "{{ $search }}"</p>
                    <p class="text-steel-500 text-xs mt-1">Try different keywords or check your filters</p>
                </div>
            @endif
        </div>
    @endif
</div>
