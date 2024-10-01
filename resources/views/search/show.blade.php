<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-3xl text-gray-800 dark:text-gray-200 leading-tight">
            Search Results
        </h2>
    </x-slot>
    <div class="mx-auto container bg-slate-400">
        @foreach ($users as $user)
            <h1>{{ $user->username }}</h1>
        @endforeach
    </div>
</x-app-layout>
