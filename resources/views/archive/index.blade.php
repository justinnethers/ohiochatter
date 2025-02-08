<x-app-layout>
    <x-slot name="title">Archive</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-3xl text-gray-800 dark:text-gray-200 leading-tight">
            Archive
        </h2>
    </x-slot>
    <div class="mx-auto container bg-slate-800 rounded-lg mt-12 text-white p-8">
        <ul>
            @foreach($forums as $forum)
                @dump($forum)
                <li class="mb-2 bg-slate-700 rounded p-3 px-4">
                    <a href="archive/{{ $forum->forumid }}">
                        <h3 class="text-xl">{{ $forum->title }}</h3>
                        <p class="text-sm">{{ $forum->description }}</p>
                        <p>{{ $forum->lastthread }}</p>
                        <p>{{$forum->lastposter}}</p>
                    </a>
                </li>
            @endforeach
        </ul>

{{--        {{ $threads->links() }}--}}
    </div>
</x-app-layout>
