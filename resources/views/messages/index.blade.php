<x-app-layout>
    <x-slot name="title">Private Messages</x-slot>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-3xl text-gray-800 dark:text-gray-200 leading-tight">
                Private Messages
            </h2>
            <x-nav-link href="{{ route('messages.create') }}">New Message</x-nav-link>
        </div>
    </x-slot>

    <div>
        <div class="md:rounded-lg md:bg-gray-800 p-2 md:p-8 md:mt-4">
            <section class="container">
                @forelse($threads as $thread)
                    <article class="bg-gray-700 p-3 md:px-4 md:pt-4 md:pb-5 text-gray-100 font-body rounded md:rounded-md mb-2 md:mb-6 shadow-lg">
                        <a class="text-2xl hover:underline text-gray-200" href="{{ route('messages.show', $thread) }}">
                            @if($thread->isUnread(auth()->id()))
                                <span class="font-bold">{{ $thread->subject }}</span>
                            @else
                                {{ $thread->subject }}
                            @endif
                        </a>

                        <div class="md:flex text-lg justify-between rounded md:rounded-md px-2 mt-3 mb-4 bg-gray-800 shadow">
                            <div class="flex items-center space-x-2">
                                @if($firstMessage = $thread->messages->first())
                                    <x-avatar size="8" :avatar-path="$firstMessage->user->avatar_path" />
                                    <span>
                                        started {{ $thread->created_at->setTimezone(auth()->user()->timezone ?? null)->diffForHumans() }}
                                        by <br class="md:hidden"><a href="/profiles/{{ $firstMessage->user->username }}" class="text-blue-500 hover:underline">{{ $firstMessage->user->username }}</a>
                                    </span>
                                @endif
                            </div>

                            <hr class="border-gray-700 border-2 md:hidden mt-2 mb-1.5">

                            <div class="flex items-center justify-end space-x-2 bg-main-color posted-by-when rounded shadow md:shadow-none md:p-2 md:p-0 md:m-0">
                                @if($lastMessage = $thread->messages->last())
                                    @if($firstMessage && $lastMessage->user_id !== $firstMessage->user_id)
                                        <div class="text-right">
                                            <span class="md:mr-1">last reply was</span>
                                            <span class="md:mr-1">
                                                {{ $lastMessage->created_at->setTimezone(auth()->user()->timezone ?? null)->diffForHumans() }}
                                                by<br class="md:hidden">
                                            </span>
                                            <a href="/profiles/{{ $lastMessage->user->username }}" class="text-blue-500 hover:underline">{{ $lastMessage->user->username }}</a>
                                        </div>
                                        <x-avatar size="8" :avatar-path="$lastMessage->user->avatar_path" />
                                    @else
                                        <div>
                                            <span class="md:mr-1">no replies yet</span>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <div class="text-lg md:mt-2">
                            <div class="flex items-center space-x-4 text-gray-400">
                                <span>{{ $thread->participants->count() }} participants</span>
                                <span>{{ $thread->messages->count() }} messages</span>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="bg-gray-700 p-3 md:px-4 md:pt-4 md:pb-5 text-gray-100 font-body rounded md:rounded-md mb-2 md:mb-6 shadow-lg">
                        No messages found.
                    </div>
                @endforelse
            </section>

            {{ $threads->links() }}
        </div>
    </div>
</x-app-layout>
