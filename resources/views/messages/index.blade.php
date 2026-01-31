<x-app-layout :seo="$seo ?? null">
    <x-slot name="title">Private Messages</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-white leading-tight flex items-center gap-3">
                <span class="hidden md:inline-block w-1 h-6 bg-accent-500 rounded-full"></span>
                Private Messages
            </h2>
            <a href="{{ route('messages.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-accent-500 to-accent-600 border border-transparent rounded-lg font-semibold text-sm text-white tracking-wide shadow-lg shadow-accent-500/25 hover:shadow-accent-500/40 hover:from-accent-600 hover:to-accent-700 hover:scale-[1.02] transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Message
            </a>
        </div>
    </x-slot>

    <div class="container mx-auto">
        <div class="md:rounded-2xl md:bg-gradient-to-br md:from-steel-800/50 md:to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-2 md:p-8 md:mt-4">
            @forelse($threads as $thread)
                <article class="group bg-gradient-to-br from-steel-800 to-steel-850 p-4 md:p-5 text-steel-100 font-body rounded-xl mb-3 md:mb-4 shadow-lg shadow-black/20 border border-steel-700/50 hover:border-steel-600 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 relative overflow-hidden">
                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b from-accent-400 to-accent-600 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                    <a class="text-lg md:text-xl hover:text-accent-400 text-white font-semibold transition-colors duration-200 block" href="{{ route('messages.show', $thread) }}">
                        @if($thread->isUnread(auth()->id()))
                            <span class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-accent-500 animate-pulse"></span>
                                {{ $thread->subject }}
                            </span>
                        @else
                            {{ $thread->subject }}
                        @endif
                    </a>

                    <div class="md:flex text-sm md:text-base justify-between rounded-lg my-3 bg-steel-900/50 shadow-inner divide-y md:divide-y-0 divide-steel-700/50">
                        <div class="flex items-center space-x-2 py-2 px-3">
                            @if($firstMessage = $thread->messages->first())
                                <x-avatar size="6" :avatar-path="$firstMessage->user->avatar_path" />
                                <span class="text-steel-300">
                                    started {{ $thread->created_at->setTimezone(auth()->user()->timezone ?? config('app.timezone'))->diffForHumans() }}
                                    by <a href="/profiles/{{ $firstMessage->user->username }}" class="text-accent-400 hover:text-accent-300 font-medium transition-colors">{{ $firstMessage->user->username }}</a>
                                </span>
                            @endif
                        </div>

                        @if($lastMessage = $thread->messages->last())
                            @if($firstMessage && $lastMessage->user_id !== $firstMessage->user_id)
                                <div class="flex items-center justify-end space-x-2 py-2 px-3">
                                    <div class="text-right text-steel-300">
                                        <span class="text-steel-400">{{ $lastMessage->created_at->setTimezone(auth()->user()->timezone ?? config('app.timezone'))->diffForHumans() }}</span>
                                        <a href="/profiles/{{ $lastMessage->user->username }}" class="text-accent-400 hover:text-accent-300 font-medium transition-colors">{{ $lastMessage->user->username }}</a>
                                    </div>
                                    <x-avatar size="6" :avatar-path="$lastMessage->user->avatar_path" />
                                </div>
                            @else
                                <div class="flex items-center justify-end py-2 px-3">
                                    <span class="text-steel-400">no replies yet</span>
                                </div>
                            @endif
                        @endif
                    </div>

                    <div class="flex flex-wrap text-sm text-steel-400 mt-2">
                        <span class="mr-4">{{ $thread->participants->count() }} participants</span>
                        <span>{{ $thread->messages->count() }} messages</span>
                    </div>
                </article>
            @empty
                <div class="bg-gradient-to-br from-steel-800 to-steel-850 p-6 text-steel-300 font-body rounded-xl shadow-lg shadow-black/20 border border-steel-700/50 text-center">
                    <svg class="w-12 h-12 mx-auto mb-3 text-steel-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                    No messages found. Start a conversation!
                </div>
            @endforelse

            <div class="mt-4">
                {{ $threads->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
