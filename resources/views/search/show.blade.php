<x-app-layout>
    <x-slot name="title">Search for "{{ $query }}"</x-slot>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-gray-800 dark:text-gray-200 leading-tight">
                Search Results
            </h2>
        </div>
    </x-slot>

    <div>
        <div class="md:rounded-lg md:bg-gray-800 p-2 md:p-8 md:mt-4">
            <div class="container">
                <div class="mb-6">
                    <x-search-form/>
                </div>

                @if($query)
                    <div class="mb-4 text-gray-100">
                        <h2 class="text-2xl font-semibold">Search results for: "{{ $query }}"</h2>
                    </div>

                    @if($threads->count() > 0)
                        <div class="mb-8">
                            <h3 class="text-xl font-bold text-gray-200 mb-4">Threads</h3>
                            <div class="space-y-4 mb-8">
                                @foreach($threads as $thread)
                                    <article class="bg-gray-700 p-4 text-gray-100 font-body rounded-md shadow-lg">
                                        <a href="{{ $thread->path() }}" class="text-2xl hover:underline text-gray-200">
                                            @if($thread->locked)
                                                <span class="text-xl">🔒</span>
                                            @endif
                                            {{ $thread->title }}
                                        </a>
                                        <p class="text-white mt-2">{{ Str::limit(strip_tags($thread->body), 200) }}</p>
                                        <div class="text-sm text-gray-100 mt-2 flex bg-gray-800 p-1 px-2 rounded shadow items-center">
                                            <span>Posted by</span>&nbsp;
                                            <span class="flex gap-1 items-center">
                                                <x-avatar :size="6" :avatar-path="$thread->owner->avatar_path"/>
                                                <a href="/profiles/{{ $thread->owner->username }}" class="text-blue-500 hover:underline">{{ $thread->owner->username }}</a>
                                            </span>&nbsp;
                                            <span class="flex gap-2">
                                                <span>{{ \Carbon\Carbon::parse($thread->created_at)->setTimezone((auth()->check() ? auth()->user()->timezone : null))->diffForHumans() }}</span>
                                                <span>in</span>
                                                <x-thread.forum-tag :thread="$thread"/>
                                            </span>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
{{--                            {{ $threads->links('pagination::tailwind') }}--}}
                            {{ $threads->appends(['q' => $query])->setPath(request()->url())->links() }}
                        </div>
                    @endif

                    @if($posts->count() > 0)
                        <div class="mb-8">
                            <h3 class="text-xl font-bold text-gray-200 mb-4">Posts</h3>
                            <div class="space-y-4 mb-8">
                                @foreach($posts as $post)
                                    <article class="bg-gray-700 p-4 text-gray-100 font-body rounded-md shadow-lg">
                                        <div class="text-2xl mb-2">
                                            Reply in <a href="{{ $post->thread->path() }}" class="hover:underline text-gray-200">{{ $post->thread->title }}</a>
                                        </div>
                                        <p class="text-white mt-2">{{ Str::limit(strip_tags($post->body), 200) }}</p>
                                        <div class="text-sm text-gray-100 mt-2 flex bg-gray-800 p-1 px-2 rounded shadow items-center">
                                            <span>Posted by</span>&nbsp;
                                            <span class="flex gap-1 items-center">
                                                <x-avatar :size="6" :avatar-path="$post->owner->avatar_path"/>
                                                <a href="/profiles/{{ $post->owner->username }}" class="text-blue-500 hover:underline">{{ $post->owner->username }}</a>
                                            </span>&nbsp;
                                            <span class="flex gap-2">
                                                <span>{{ \Carbon\Carbon::parse($post->created_at)->setTimezone((auth()->check() ? auth()->user()->timezone : null))->diffForHumans() }}</span>
                                                <span>in</span>
                                                <x-thread.forum-tag :thread="$post->thread"/>
                                            </span>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
{{--                            {{ $posts->links('pagination::tailwind') }}--}}
                            {{ $posts->appends(['q' => $query])->setPath(request()->url())->links() }}

                        </div>
                    @endif

                    @if($users->count() > 0)
                        <div class="mb-8">
                            <h3 class="text-xl font-bold text-gray-200 mb-4">Users</h3>
                            <div class="space-y-4">
                                @foreach($users as $user)
                                    <article class="bg-gray-700 p-4 text-gray-100 font-body rounded-md shadow-lg">
                                        <div class="flex items-center space-x-4">
                                            <img src="{{ $user->avatar_path }}" alt="{{ $user->username }}" class="w-12 h-12 rounded-full">
                                            <div>
                                                <div class="text-lg font-semibold">{{ $user->username }}</div>
                                                <div class="text-sm text-gray-500">Joined {{ $user->created_at->diffForHumans() }}</div>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
{{--                            {{ $users->links('pagination::tailwind') }}--}}
                            {{ $users->appends(['q' => $query])->setPath(request()->url())->links() }}
                        </div>
                    @endif

                    @if($threads->isEmpty() && $posts->isEmpty() && $users->isEmpty())
                        <div class="text-center py-8 text-gray-400">
                            No results found for "{{ $query }}"
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
