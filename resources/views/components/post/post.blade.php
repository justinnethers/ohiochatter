@if ($poll)
    <x-poll.show :$poll :$hasVoted :$voteCount />
@endif
<article id="reply-{{ $post->id }}" class="bg-gray-800 text-white mb-4 md:flex rounded md:rounded-lg">
    <x-post.owner :owner="$post->owner" />
    <div class="flex-1">
        <x-post.header :date="$post->created_at" />
        <div class="prose prose-invert prose-lg p-4 md:p-8">
            {!! $post->body !!}
        </div>
    </div>
</article>
