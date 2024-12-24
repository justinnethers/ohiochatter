<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-3xl text-gray-800 dark:text-gray-200 leading-tight">
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

            <div class="flex flex-col xl:flex-row gap-4">
                {{ $threads->links() }}
                <div class="flex-1">
                    <x-search-form />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
