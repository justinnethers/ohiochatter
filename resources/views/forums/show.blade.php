<x-app-layout>
    <x-slot name="title">{{ $forum->name }}</x-slot>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-3xl text-gray-200 dark:text-gray-200 leading-tight">
                {{ $forum->name }}
            </h2>
            @if (auth()->check())
                <x-nav-link
                    href="/forums/{{ $forum->slug }}/threads/create"
                >Create Thread</x-nav-link>
            @endif
        </div>
    </x-slot>

    <div class="md:rounded-lg md:bg-gray-800 p-2 md:p-8 md:pt-4 md:mt-4">
        {{ $threads->links('pagination::tailwind', ['top' => true]) }}
        <section>
            @foreach ($threads as $thread)
                <x-thread.listing :$thread :$forum />
            @endforeach
        </section>
        {{ $threads->links('pagination::tailwind', ['top' => false]) }}
    </div>
</x-app-layout>
