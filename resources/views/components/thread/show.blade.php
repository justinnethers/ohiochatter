<div>
    <x-breadcrumbs :forum="$thread->forum" />

    <div class="p-2 pt-0 md:p-0">
        @if($thread->poll)
            <livewire:poll-component :poll="$thread->poll" />
        @endif

        @if (app('request')->input('page') == 1 || !app('request')->input('page'))
            <livewire:post-component :post="$thread" />
{{--            <x-post.post :post="$thread" :$poll :$hasVoted :$voteCount />--}}
        @endif

        @foreach ($replies as $post)
                <livewire:post-component :$post />
{{--            <x-post.post :$post :poll="false" :hasVoted="false" :voteCount="0" />--}}
        @endforeach

        @if (auth()->check())
            <form
                class="bg-gray-800 p-8 rounded-lg shadow mb-8"
                action="{{ request()->url() }}/replies"
                method="POST"
            >
                @csrf
                <x-wysiwyg id="body" wire:model.defer="body" />
                <div class="h-4"></div>
                <div class="flex justify-between">
                    <x-primary-button>Submit Post</x-primary-button>
                    <livewire:thread-lock-toggle :thread="$thread" />
                </div>
            </form>


        @endif
    </div>
</div>
