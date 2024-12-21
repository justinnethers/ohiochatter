<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-3xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $thread->subject }}
            </h2>
            <x-nav-link href="{{ route('messages.index') }}">Back to Messages</x-nav-link>
        </div>
    </x-slot>

    <div>
        <div class="md:rounded-lg md:bg-gray-800 p-2 md:p-8 md:mt-4">
            <section class="container space-y-6">
                @foreach($thread->messages as $message)
                    <article class="bg-gray-700 p-3 md:px-4 md:pt-4 md:pb-5 text-gray-100 font-body rounded md:rounded-md shadow-lg">
                        <div class="flex items-center space-x-3 mb-4">
                            <x-avatar size="10" :avatar-path="$message->user->avatar_path" />
                            <div>
                                <a href="/profiles/{{ $message->user->username }}" class="text-blue-500 hover:underline text-lg">
                                    {{ $message->user->username }}
                                </a>
                                <div class="text-sm text-gray-400">
                                    {{ $message->created_at->setTimezone(auth()->user()->timezone ?? null)->format('M j, Y g:i A') }}
                                </div>
                            </div>
                        </div>

                        <div class="prose prose-invert max-w-none">
                            {!! nl2br(e($message->body)) !!}
                        </div>
                    </article>
                @endforeach

                <div class="bg-gray-700 p-3 md:px-4 md:pt-4 md:pb-5 text-gray-100 font-body rounded md:rounded-md shadow-lg">
                    <form action="{{ route('messages.add_message', $thread) }}" method="POST">
                        @csrf
                        <div>
                            <label for="body" class="sr-only">Your message</label>
                            <textarea
                                name="body"
                                id="body"
                                rows="3"
                                class="w-full rounded-md border-gray-600 bg-gray-800 text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Write your reply..."
                                required
                            ></textarea>
                        </div>

                        @if($users->count() > 0)
                            <div class="mt-4">
                                <label for="recipients" class="block text-sm font-medium text-gray-200">Add Participants</label>
                                <select
                                    id="recipients"
                                    name="recipients[]"
                                    multiple
                                    class="mt-1 block w-full rounded-md border-gray-600 bg-gray-800 text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->username }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="mt-4 flex justify-end">
                            <x-primary-button>
                                Reply
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
