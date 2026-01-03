@if ($poll)
    <x-poll.show :$poll :$hasVoted :$voteCount />
@endif
<x-post.card :id="'reply-' . $post->id">
    <x-post.owner :owner="$post->owner" />
    <div class="flex-1 flex flex-col relative">
        <x-post.header :date="$post->created_at" />

        {{-- Post content --}}
        <div class="prose prose-invert prose-lg p-4 md:p-8 flex-1 post-body">
            {!! $post->formatted_body !!}
        </div>

        {{-- Mobile date --}}
        <div class="md:hidden px-4 pb-2 text-sm text-steel-500">
            {{ \Carbon\Carbon::parse($post->created_at)->toDayDateTimeString() }}
        </div>

        {{-- Actions footer --}}
        <div class="flex justify-end p-4 pt-0 space-x-4 border-t border-steel-700/30 mt-4 mx-4 md:mx-8">
            <livewire:reputation :post="$post" />
        </div>
    </div>
</x-post.card>
