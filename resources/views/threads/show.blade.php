<x-app-layout>
    <x-slot name="meta">{{ $thread->meta_description }}</x-slot>
    <x-slot name="title">{{ $thread->title }}</x-slot>
    <x-slot name="header">
        <h2 class="text-lg md:text-xl font-bold text-white leading-tight flex items-center gap-3">
            <span class="hidden md:inline-block w-1 h-6 bg-accent-500 rounded-full"></span>
            {{ $thread->title }}
        </h2>
    </x-slot>

    <div class="container mx-auto">
        <div class="md:rounded-2xl md:bg-gradient-to-br md:from-steel-800/50 md:to-steel-900/50 md:backdrop-blur-sm md:border md:border-steel-700/30 p-2 md:p-8 md:pt-6 md:mt-4">
            {{ $replies->links('pagination::tailwind', ['top' => true]) }}
            <x-thread.show :$thread :$replies :$poll :$hasVoted :$voteCount/>
            {{ $replies->links() }}
        </div>
    </div>
</x-app-layout>
