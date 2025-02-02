<x-app-layout>
    <x-slot name="title">All Threads</x-slot>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-gray-800 dark:text-gray-200 leading-tight">
                All Threads
            </h2>
            <x-nav-link
                href="/threads/create"
            >Create Thread</x-nav-link>
        </div>

    </x-slot>

    <div>
        <div class="md:rounded-lg md:bg-gray-800 p-2 md:p-8 md:mt-4">
            <section class="container">
                @foreach ($threads as $thread)
                    <x-thread.listing :$thread />
                @endforeach
            </section>
            {{ $threads->links() }}
        </div>
    </div>
</x-app-layout>
