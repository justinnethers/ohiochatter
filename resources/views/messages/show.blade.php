<x-app-layout>
    <x-slot name="title">{{ $thread->subject }}</x-slot>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-xl text-white leading-tight flex items-center gap-3">
                <span class="hidden md:inline-block w-1 h-6 bg-accent-500 rounded-full"></span>
                {{ $thread->subject }}
            </h2>
            <a href="{{ route('messages.index') }}" class="text-steel-300 hover:text-white transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Back to Messages
            </a>
        </div>
    </x-slot>

    <div class="container mx-auto">
        <div class="md:rounded-2xl md:bg-gradient-to-br md:from-steel-800/50 md:to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-2 md:p-8 md:mt-4 space-y-4">
            <!-- Participants List -->
            <div class="bg-gradient-to-br from-steel-800 to-steel-850 p-4 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50">
                <h3 class="text-sm font-semibold text-steel-400 uppercase tracking-wide mb-3">Participants</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($thread->participants as $participant)
                        <div class="flex items-center gap-2 bg-steel-900/50 rounded-full px-3 py-1.5 border border-steel-700/30">
                            <x-avatar size="5" :avatar-path="$participant->user->avatar_path" />
                            <span class="text-sm text-steel-200">{{ $participant->user->username }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Messages -->
            @foreach($thread->messages as $message)
                <x-post.card>
                    <x-post.owner
                        :owner="$message->user"
                        :username="$message->user->username"
                        :usertitle="$message->user->usertitle ?? null"
                        :avatar-path="$message->user->avatar_path"
                        :posts-count="$message->user->posts_count ?? null"
                    />
                    <div class="flex-1 flex flex-col relative">
                        <x-post.header :date="$message->created_at" />

                        <div class="prose prose-invert prose-lg p-4 md:p-8 flex-1 post-body">
                            {!! $message->body !!}
                        </div>
                    </div>
                </x-post.card>
            @endforeach

            <!-- Reply Form -->
            <div class="bg-gradient-to-br from-steel-800 to-steel-850 p-4 md:p-6 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50">
                <form action="{{ route('messages.add_message', $thread) }}" method="POST">
                    @csrf
                    <div>
                        <x-wysiwyg name="body" id="body" />
                    </div>
                    <div class="h-4"></div>
                    <div class="flex justify-end">
                        <x-primary-button>
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                            Reply
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
