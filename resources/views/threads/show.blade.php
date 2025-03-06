<x-app-layout>
    <x-slot name="meta">{{ $thread->meta_description }}</x-slot>
    <x-slot name="title">{{ $thread->title }}</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-gray-200 dark:text-gray-200 leading-tight">
            {{ $thread->title }}
        </h2>
    </x-slot>

    <div class="container mx-auto">
        <x-thread.show :$thread :$replies :$poll :$hasVoted :$voteCount/>

        {{ $replies->onEachSide(3)->links() }}

        <div class="h-8"></div>
    </div>


</x-app-layout>
