<div
    x-data="{
        open: false,
        enabledTypes: {
            threads: true,
            posts: true,
            users: true,
            archive: true,
            guides: true,
            locations: true,
        },
        showFilters: false,
        init() {
            // Load from localStorage
            const saved = localStorage.getItem('searchEnabledTypes');
            if (saved) {
                try {
                    this.enabledTypes = JSON.parse(saved);
                } catch (e) {}
            }
            // Sync to Livewire
            $wire.enabledTypes = this.enabledTypes;

            // Watch for changes and persist
            $watch('enabledTypes', (value) => {
                localStorage.setItem('searchEnabledTypes', JSON.stringify(value));
                $wire.enabledTypes = value;
            });
        },
        toggleType(type) {
            this.enabledTypes[type] = !this.enabledTypes[type];
        }
    }"
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
            placeholder="Search discussions, users, guides..."
            class="w-full px-4 py-2.5 pl-10 pr-10 bg-steel-950 border border-steel-600 rounded-lg text-white placeholder-steel-400 focus:outline-none focus:border-accent-500 focus:ring-1 focus:ring-accent-500"
        >
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-steel-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>

        {{-- Filter Toggle Button --}}
        <button
            type="button"
            @click.stop="showFilters = !showFilters"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-steel-400 hover:text-steel-200 transition-colors"
            title="Filter search types"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
            </svg>
        </button>
    </div>

    {{-- Filter Toggles --}}
    <div
        x-show="showFilters"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="absolute z-[100] mt-1 w-full bg-gradient-to-b from-steel-800 to-steel-850 rounded-lg shadow-xl shadow-black/30 border border-steel-700/50 p-3"
    >
        <div class="text-xs text-steel-400 mb-2 font-medium uppercase tracking-wide">Search in:</div>
        <div class="flex flex-wrap gap-2">
            <template x-for="[type, label] in [['threads', 'Threads'], ['posts', 'Posts'], ['users', 'Users'], ['archive', 'Archive'], ['guides', 'Guides'], ['locations', 'Locations']]">
                <label class="inline-flex items-center cursor-pointer">
                    <input
                        type="checkbox"
                        :checked="enabledTypes[type]"
                        @change="toggleType(type)"
                        class="sr-only"
                    >
                    <span
                        :class="enabledTypes[type] ? 'bg-accent-500/20 border-accent-500 text-accent-300' : 'bg-steel-900/50 border-steel-600 text-steel-400'"
                        class="px-2.5 py-1 text-xs font-medium rounded-full border transition-colors"
                        x-text="label"
                    ></span>
                </label>
            </template>
        </div>
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
                <div class="max-h-[70vh] overflow-y-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-0 divide-y md:divide-y-0 md:divide-x divide-steel-700/50">

                        {{-- Column 1: Threads & Posts --}}
                        <div class="p-4 space-y-4">
                            {{-- Threads --}}
                            @if(isset($results['threads']))
                                <div>
                                    <h4 class="text-xs font-semibold text-steel-400 uppercase tracking-wide mb-2 flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                        </svg>
                                        Threads
                                    </h4>
                                    <div class="space-y-1">
                                        @foreach($results['threads'] as $thread)
                                            <a href="{{ $thread->path() }}" class="block p-2 rounded-lg hover:bg-steel-700/50 transition-colors group">
                                                <div class="text-sm text-white group-hover:text-accent-300 font-medium line-clamp-1">{{ $thread->title }}</div>
                                                <div class="text-xs text-steel-500">{{ $thread->forum->name ?? 'Forum' }}</div>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Posts --}}
                            @if(isset($results['posts']))
                                <div>
                                    <h4 class="text-xs font-semibold text-steel-400 uppercase tracking-wide mb-2 flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Posts
                                    </h4>
                                    <div class="space-y-1">
                                        @foreach($results['posts'] as $post)
                                            <a href="{{ $post->thread?->path() }}#post-{{ $post->id }}" class="block p-2 rounded-lg hover:bg-steel-700/50 transition-colors group">
                                                <div class="text-sm text-steel-200 group-hover:text-white line-clamp-1">{{ Str::limit(strip_tags($post->body), 60) }}</div>
                                                <div class="text-xs text-steel-500">
                                                    in {{ $post->thread?->title ?? 'Thread' }} &bull; {{ $post->owner?->username ?? 'Unknown' }}
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Column 2: Users & Archive --}}
                        <div class="p-4 space-y-4">
                            {{-- Users --}}
                            @if(isset($results['users']))
                                <div>
                                    <h4 class="text-xs font-semibold text-steel-400 uppercase tracking-wide mb-2 flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        Users
                                    </h4>
                                    <div class="space-y-1">
                                        @foreach($results['users'] as $user)
                                            <a href="{{ route('profile.show', $user) }}" class="flex items-center gap-2 p-2 rounded-lg hover:bg-steel-700/50 transition-colors group">
                                                <x-avatar :avatar-path="$user->avatar_path" :size="6" />
                                                <span class="text-sm text-white group-hover:text-accent-300 font-medium">{{ $user->username }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Archive --}}
                            @if(isset($results['archive']))
                                <div>
                                    <h4 class="text-xs font-semibold text-steel-400 uppercase tracking-wide mb-2 flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                        </svg>
                                        Archive
                                    </h4>
                                    <div class="space-y-1">
                                        @foreach($results['archive'] as $item)
                                            @if($item['type'] === 'thread')
                                                <a href="{{ route('archive.thread', $item['item']) }}" class="block p-2 rounded-lg hover:bg-steel-700/50 transition-colors group">
                                                    <div class="text-sm text-white group-hover:text-accent-300 font-medium line-clamp-1">{{ $item['item']->title }}</div>
                                                    <div class="text-xs text-steel-500">{{ $item['item']->forum?->title ?? 'Archive' }}</div>
                                                </a>
                                            @else
                                                <a href="{{ route('archive.thread', $item['item']->thread) }}" class="block p-2 rounded-lg hover:bg-steel-700/50 transition-colors group">
                                                    <div class="text-sm text-steel-200 group-hover:text-white line-clamp-1">{{ Str::limit(strip_tags($item['item']->pagetext), 60) }}</div>
                                                    <div class="text-xs text-steel-500">in {{ $item['item']->thread?->title ?? 'Thread' }}</div>
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Column 3: Guides & Locations --}}
                        <div class="p-4 space-y-4">
                            {{-- Guides --}}
                            @if(isset($results['guides']))
                                <div>
                                    <h4 class="text-xs font-semibold text-steel-400 uppercase tracking-wide mb-2 flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                        </svg>
                                        Guides
                                    </h4>
                                    <div class="space-y-1">
                                        @foreach($results['guides'] as $guide)
                                            <a href="{{ route('guide.show', $guide) }}" class="block p-2 rounded-lg hover:bg-steel-700/50 transition-colors group">
                                                <div class="text-sm text-white group-hover:text-accent-300 font-medium line-clamp-1">{{ $guide->title }}</div>
                                                @if($guide->location_name)
                                                    <div class="text-xs text-steel-500">{{ $guide->location_name }}</div>
                                                @endif
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Locations --}}
                            @if(isset($results['locations']))
                                <div>
                                    <h4 class="text-xs font-semibold text-steel-400 uppercase tracking-wide mb-2 flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        Locations
                                    </h4>
                                    <div class="space-y-1">
                                        @foreach($results['locations'] as $location)
                                            @if($location['type'] === 'region')
                                                <a href="{{ route('region.show', $location['item']) }}" class="flex items-center gap-2 p-2 rounded-lg hover:bg-steel-700/50 transition-colors group">
                                                    <span class="text-xs px-1.5 py-0.5 bg-emerald-500/20 text-emerald-400 rounded font-medium">Region</span>
                                                    <span class="text-sm text-white group-hover:text-accent-300">{{ $location['item']->name }}</span>
                                                </a>
                                            @else
                                                <a href="{{ route('city.show', ['region' => $location['item']->county->region, 'county' => $location['item']->county, 'city' => $location['item']]) }}" class="flex items-center gap-2 p-2 rounded-lg hover:bg-steel-700/50 transition-colors group">
                                                    <span class="text-xs px-1.5 py-0.5 bg-blue-500/20 text-blue-400 rounded font-medium">City</span>
                                                    <span class="text-sm text-white group-hover:text-accent-300">{{ $location['item']->name }}</span>
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- View All Results Footer --}}
                <div class="border-t border-steel-700/50 p-3 bg-steel-900/50">
                    <a href="{{ route('search.show', ['q' => $search]) }}" class="flex items-center justify-center gap-2 text-sm text-accent-400 hover:text-accent-300 font-medium transition-colors">
                        View all results for "{{ $search }}"
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                    </a>
                </div>
            @else
                {{-- No Results --}}
                <div class="p-6 text-center">
                    <svg class="w-10 h-10 mx-auto text-steel-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <p class="text-steel-400 text-sm">No results found for "{{ $search }}"</p>
                    <p class="text-steel-500 text-xs mt-1">Try different keywords or check your filters</p>
                </div>
            @endif
        </div>
    @endif
</div>
