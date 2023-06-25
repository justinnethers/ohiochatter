<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            All Threads
        </h2>
    </x-slot>

    <div>
        <ul class="text-white">
            @unless(Auth::check())
                <p>Please <a href="/login">sign in</a> to participate in this discussion.</p>
            @endunless

            @foreach ($threads as $thread)
                <li>
                    <a href="/forums/{{ $thread->forum->slug }}/{{ $thread->slug }}">{{ $thread->title }}</a>
                </li>
            @endforeach
        </ul>
    </div>
</x-app-layout>
