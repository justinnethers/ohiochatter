<x-app-layout>
    <x-slot name="title">Search</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-3xl text-gray-800 dark:text-gray-200 leading-tight">
            Search
        </h2>
    </x-slot>
    <div>
        <form method="POST" action="/search">
            @csrf
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search for threads, posts, or users..." class="w-full p-2 rounded border border-gray-300">
        </form>
    </div>
</x-app-layout>
