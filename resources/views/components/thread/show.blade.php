<div>
    <x-breadcrumbs :forum="$thread->forum" />

    <div class="p-2 pt-0 md:p-0">

        @if (app('request')->input('page') == 1 || !app('request')->input('page'))
            <x-post.post :post="$thread" :$poll :$hasVoted :$voteCount />
        @endif

        @foreach ($replies as $post)
{{--                <livewire:post-component :$post />--}}
            <x-post.post :$post :poll="false" :hasVoted="false" :voteCount="0" />
        @endforeach

        @if (auth()->check())
            <form
                class="bg-gray-800 p-8 rounded-lg shadow mb-8"
                action="{{ request()->url() }}/replies"
                method="POST"
            >
                @csrf
{{--                <livewire:wysiwyg-editor wire:model.defer="body" :editorId="'editor-'. $thread->id" />--}}
                <x-wysiwyg wire:model.defer="body" />
                <div class="h-4"></div>
                <x-primary-button>Submit Post</x-primary-button>
            </form>
        @endif
    </div>
</div>
