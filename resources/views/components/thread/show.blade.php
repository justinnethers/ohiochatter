<div>
    <x-breadcrumbs :forum="$thread->forum" />

    <div class="p-2 pt-0 md:p-0">

        <x-post.post :post="$thread" :$poll :$hasVoted :$voteCount />
        @foreach ($replies as $post)
            <x-post.post :$post :poll="false" :hasVoted="false" :voteCount="0" />
        @endforeach

        <div class="bg-gray-800 p-8 rounded-lg shadow">
            <textarea class="rounded-lg w-full shadow-inner text-lg p-4 bg-gray-300" name="" id="" cols="30" rows="10"></textarea>
        </div>
    </div>
</div>
