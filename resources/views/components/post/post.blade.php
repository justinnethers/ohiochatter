@if ($poll)
    <x-poll.show :$poll :$hasVoted :$voteCount />
@endif
<article id="reply-{{ $post->id }}" class="bg-gradient-to-br from-steel-800 to-steel-850 text-white mb-5 md:flex rounded-xl relative border border-steel-700/50 shadow-xl shadow-black/20 overflow-hidden group">
    {{-- Subtle top accent --}}
    <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-steel-600/50 to-transparent"></div>

    <x-post.owner :owner="$post->owner" />
    <div class="flex-1 flex flex-col relative">
        <x-post.header :date="$post->created_at" />
        <div class="prose prose-invert prose-lg p-4 md:p-8 flex-1 post-body">
            {!! $post->body !!}
        </div>
        <div class="flex justify-end p-4 pt-0 space-x-4 border-t border-steel-700/30 mt-4 mx-4 md:mx-8">
            <livewire:reputation :post="$post" />
        </div>
    </div>
</article>
