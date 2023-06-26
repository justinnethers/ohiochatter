<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 dark:text-gray-200 leading-tight">
            All Threads
        </h2>
    </x-slot>

    <div>
        <div class="rounded-lg bg-gray-800 p-8 mt-4">
            <section class="container">
                @foreach ($threads as $thread)
                    <x-thread.listing :$thread />
                @endforeach
            </section>

            {{ $threads->links() }}
        </div>
    </div>
</x-app-layout>
