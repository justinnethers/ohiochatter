<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-3xl text-gray-800 dark:text-gray-200 leading-tight">
                Search Results
            </h2>
        </div>

    </x-slot>

    <div>
        <div class="md:rounded-lg md:bg-gray-800 p-2 md:p-8 md:mt-4">
            <div class="container">
                <div class="">
                    <div class="mb-6">
                        <x-search-form />
                    </div>

                    @if($query)
                        <div class="mb-4 text-gray-100">
                            <h2 class="text-2xl font-semibold">Search results for: "{{ $query }}"</h2>
                        </div>

                        @foreach($results as $label => $items)
                            @if($items->count() > 0)
                                <div class="mb-8">
                                    <h3 class="text-xl font-bold text-gray-200 mb-4">{{ $label }}</h3>
                                    <div class="space-y-4">
                                        @foreach($items as $item)
                                            <article class="bg-gray-700 p-4 text-gray-100 font-body rounded-md shadow-lg">
                                                @if($item instanceof \App\Models\Thread)
                                                    <a href="{{ $item->path() }}" class="text-2xl hover:underline text-gray-200">
                                                        @if($item->locked)
                                                            <span class="text-xl">🔒</span>
                                                        @endif
                                                        {{ $item->title }}
                                                    </a>
                                                    <p class="text-white mt-2">{{ Str::limit(strip_tags($item->body), 200) }}</p>
                                                    <div class="text-sm text-gray-100 mt-2 flex bg-gray-800 p-1 px-2 rounded shadow items-center">
                                                        <span>Posted by</span>&nbsp;
                                                        <span class="flex gap-1 items-center">
                                                            <x-avatar :size="6" :avatar-path="$item->owner->avatar_path" />
                                                            <a href="/profiles/{{ $item->owner->username }}" class="text-blue-500 hover:underline">{{ $item->owner->username }}</a>
                                                        </span>&nbsp;
                                                        <span class="flex">in&nbsp;<x-thread.forum-tag :thread="$item" /></span>
                                                    </div>
                                                @elseif($item instanceof \App\Models\Reply)
                                                    <div class="text-lg font-semibold">
                                                        Reply in <a href="{{ $item->thread->path() }}" class="hover:underline text-gray-200">{{ $item->thread->title }}</a>
                                                    </div>
                                                    <p class="text-gray-400 mt-2">{{ Str::limit(strip_tags($item->body), 200) }}</p>
                                                    <div class="text-sm text-gray-500 mt-2">
                                                        Posted by <a href="/profiles/{{ $item->owner->username }}" class="text-blue-500 hover:underline">{{ $item->owner->username }}</a>
                                                    </div>
                                                @elseif($item instanceof \App\Models\User)
                                                    <div class="flex items-center space-x-4">
                                                        <img src="{{ $item->avatar_path }}" alt="{{ $item->username }}" class="w-12 h-12 rounded-full">
                                                        <div>
                                                            <div class="text-lg font-semibold">{{ $item->username }}</div>
                                                            <div class="text-sm text-gray-500">Joined {{ $item->created_at->diffForHumans() }}</div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </article>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        <div class="mt-6">
                            {{ $results['Threads']->links('pagination::tailwind') }}
                        </div>

                        @if(collect($results)->every(fn($items) => $items->isEmpty()))
                            <div class="text-center py-8 text-gray-400">
                                No results found for "{{ $query }}"
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
