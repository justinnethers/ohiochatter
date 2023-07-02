@if ($poll)
    <x-poll.show :$poll :$hasVoted :$voteCount />
@endif
<article id="reply-{{ $post->id }}" class="bg-gray-800 text-white mb-4 md:flex rounded md:rounded-lg relative">
    <x-post.owner :owner="$post->owner" />
    <div class="flex-1 flex flex-col relative">
        <x-post.header :date="$post->created_at" />
        <div class="prose prose-invert prose-lg p-4 md:p-8 flex-1">
            {!! $post->body !!}
        </div>
        <div class="flex justify-end p-4 space-x-4">
            <x-reputation.reps :$post />
            <x-reputation.negs :$post />
        </div>
    </div>
</article>
