<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-3xl text-gray-800 dark:text-gray-200 leading-tight">
            All Threads
        </h2>
    </x-slot>
    <div>
        <ul>
            @foreach($forums as $forum)
                <li>
                    <a href="/forums/{{ $forum->slug }}">{{ $forum->name }} - {{ $forum->is_active }}</a>
                </li>
            @endforeach
        </ul>
    </div>
</x-app-layout>
