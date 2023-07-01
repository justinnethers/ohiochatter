<div>
    <x-breadcrumbs :forum="$thread->forum" />

    <div class="p-2 pt-0 md:p-0">

        <x-post.post :post="$thread" :$poll :$hasVoted :$voteCount />
        @foreach ($replies as $post)
            <x-post.post :$post :poll="false" :hasVoted="false" :voteCount="0" />
        @endforeach
    </div>
</div>
