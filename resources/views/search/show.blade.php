<x-app-layout>
    <x-slot name="title">Search for "{{ $query }}"</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-white leading-tight flex items-center gap-3">
                <span class="hidden md:inline-block w-1 h-6 bg-accent-500 rounded-full"></span>
                Search Results
            </h2>
        </div>
    </x-slot>

    <div class="container mx-auto">
        <div class="md:rounded-2xl md:bg-gradient-to-br md:from-steel-800/50 md:to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-2 md:p-8 md:mt-4">
            <div class="mb-6">
                <x-search-form />
            </div>

            @if($query)
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-white">Search results for: "{{ $query }}"</h2>
                </div>

                @if($threads->count() > 0)
                    <div class="mb-8">
                        <h3 class="text-sm font-semibold text-steel-400 uppercase tracking-wide mb-4">Threads</h3>
                        <div class="space-y-4 mb-6">
                            @foreach($threads as $thread)
                                <article class="group bg-gradient-to-br from-steel-800 to-steel-850 p-4 md:p-5 text-steel-100 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50 hover:border-steel-600 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 relative overflow-hidden">
                                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-accent-400 to-accent-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    <a href="{{ $thread->path() }}" class="text-lg md:text-xl font-semibold text-white hover:text-accent-300 transition-colors">
                                        @if($thread->locked)
                                            <span class="text-lg mr-1">🔒</span>
                                        @endif
                                        {{ $thread->title }}
                                    </a>
                                    <p class="text-steel-300 mt-2">{{ Str::limit(strip_tags($thread->body), 200) }}</p>
                                    <div class="rounded-lg bg-steel-900/50 shadow-inner p-3 mt-3">
                                        <div class="text-sm text-steel-400 flex flex-wrap items-center gap-2">
                                            <span>Posted by</span>
                                            <span class="flex gap-1.5 items-center">
                                                <x-avatar :size="6" :avatar-path="$thread->owner->avatar_path" />
                                                <a href="/profiles/{{ $thread->owner->username }}" class="text-accent-400 hover:text-accent-300 font-medium transition-colors">{{ $thread->owner->username }}</a>
                                            </span>
                                            <span class="text-steel-500">·</span>
                                            <span>{{ \Carbon\Carbon::parse($thread->created_at)->setTimezone((auth()->check() ? auth()->user()->timezone : null))->diffForHumans() }}</span>
                                            <span class="text-steel-500">·</span>
                                            <span class="flex gap-1 items-center">
                                                in <x-thread.forum-tag :thread="$thread" />
                                            </span>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                        {{ $threads->appends(['q' => $query])->setPath(request()->url())->links() }}
                    </div>
                @endif

                @if($posts->count() > 0)
                    <div class="mb-8">
                        <h3 class="text-sm font-semibold text-steel-400 uppercase tracking-wide mb-4">Posts</h3>
                        <div class="space-y-4 mb-6">
                            @foreach($posts as $post)
                                <article class="group bg-gradient-to-br from-steel-800 to-steel-850 p-4 md:p-5 text-steel-100 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50 hover:border-steel-600 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 relative overflow-hidden">
                                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-accent-400 to-accent-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    <div class="text-lg md:text-xl font-semibold mb-2">
                                        <span class="text-steel-400">Reply in</span>
                                        <a href="{{ $post->thread->path() }}" class="text-white hover:text-accent-300 transition-colors ml-1">{{ $post->thread->title }}</a>
                                    </div>
                                    <p class="text-steel-300 mt-2">{{ Str::limit(strip_tags($post->body), 200) }}</p>
                                    <div class="rounded-lg bg-steel-900/50 shadow-inner p-3 mt-3">
                                        <div class="text-sm text-steel-400 flex flex-wrap items-center gap-2">
                                            <span>Posted by</span>
                                            <span class="flex gap-1.5 items-center">
                                                <x-avatar :size="6" :avatar-path="$post->owner->avatar_path" />
                                                <a href="/profiles/{{ $post->owner->username }}" class="text-accent-400 hover:text-accent-300 font-medium transition-colors">{{ $post->owner->username }}</a>
                                            </span>
                                            <span class="text-steel-500">·</span>
                                            <span>{{ \Carbon\Carbon::parse($post->created_at)->setTimezone((auth()->check() ? auth()->user()->timezone : null))->diffForHumans() }}</span>
                                            <span class="text-steel-500">·</span>
                                            <span class="flex gap-1 items-center">
                                                in <x-thread.forum-tag :thread="$post->thread" />
                                            </span>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                        {{ $posts->appends(['q' => $query])->setPath(request()->url())->links() }}
                    </div>
                @endif

                @if($users->count() > 0)
                    <div class="mb-8">
                        <h3 class="text-sm font-semibold text-steel-400 uppercase tracking-wide mb-4">Users</h3>
                        <div class="space-y-4">
                            @foreach($users as $user)
                                <article class="group bg-gradient-to-br from-steel-800 to-steel-850 p-4 md:p-5 text-steel-100 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50 hover:border-steel-600 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 relative overflow-hidden">
                                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-accent-400 to-accent-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                    <a href="/profiles/{{ $user->username }}" class="flex items-center gap-4">
                                        <x-avatar :size="12" :avatar-path="$user->avatar_path" />
                                        <div>
                                            <div class="text-lg font-semibold text-accent-400 hover:text-accent-300 transition-colors">{{ $user->username }}</div>
                                            <div class="text-sm text-steel-400">Joined {{ $user->created_at->diffForHumans() }}</div>
                                        </div>
                                    </a>
                                </article>
                            @endforeach
                        </div>
                        <div class="mt-6">
                            {{ $users->appends(['q' => $query])->setPath(request()->url())->links() }}
                        </div>
                    </div>
                @endif

                @if($threads->isEmpty() && $posts->isEmpty() && $users->isEmpty())
                    <div class="bg-gradient-to-br from-steel-800 to-steel-850 p-6 text-steel-300 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50 text-center">
                        <svg class="w-12 h-12 mx-auto mb-3 text-steel-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        No results found for "{{ $query }}"
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
