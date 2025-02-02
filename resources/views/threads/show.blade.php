<x-app-layout>
    <x-slot name="title">{{ $thread->title }}</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-gray-800 dark:text-gray-200 leading-tight">
            {{ $thread->title }}
        </h2>
    </x-slot>

    <x-thread.show :$thread :$replies :$poll :$hasVoted :$voteCount />

    {{ $replies->onEachSide(3)->links() }}

    <div class="h-8"></div>
</x-app-layout>
