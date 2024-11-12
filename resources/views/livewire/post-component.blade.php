@if ($poll)
    <x-poll.show :$poll :$hasVoted :$voteCount />
@endif
<article id="reply-{{ $post->id }}" class="bg-gray-800 text-white mb-4 md:flex rounded md:rounded-lg relative">
    <x-post.owner :owner="$post->owner" />
    <div class="flex-1 flex flex-col relative">
        <x-post.header :date="$post->created_at">
            <!-- Edit Mode Toggle Button -->
            <div class="flex gap-2 text-xs font-semibold">
                @if ($editMode)
                    <button wire:click="save" class="text-green-950 hover:text-white bg-green-500 hover:bg-green-700 py-1 px-2 rounded">
                        Save
                    </button>
                @endif
                <button wire:click="toggleEditMode" class="text-yellow-950 hover:text-white bg-yellow-500 hover:bg-yellow-700 py-1 px-2 rounded">
                    {{ $editMode ? 'Cancel' : 'Edit' }}
                </button>
            </div>
        </x-post.header>

        <div class="prose prose-invert prose-lg p-4 md:p-8 flex-1">
            @if ($editMode)
                <livewire:wysiwyg-editor wire:model.defer="body" :editorId="'editor-'. $post->id" />
            @else
                {!! $post->body !!}
            @endif
        </div>
        <div class="flex justify-end p-4 space-x-4">
            <livewire:reputation :post="$post" />
        </div>
    </div>
</article>
