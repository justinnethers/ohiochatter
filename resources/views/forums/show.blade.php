<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-3xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $forum->name }}
        </h2>
    </x-slot>

    <div class="md:rounded-lg md:bg-gray-800 p-2 md:p-8 md:mt-4">
        <section class="container">
            @foreach ($threads as $thread)
                <x-thread.listing :$thread :$forum />
            @endforeach
        </section>

        {{ $threads->links() }}
    </div>
</x-app-layout>
