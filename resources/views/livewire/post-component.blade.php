@if ($poll)
    <x-poll.show :$poll :$hasVoted :$voteCount />
@endif
<article
    id="reply-{{ $post->id }}"
    class="bg-gray-800 text-white mb-4 md:flex rounded md:rounded-lg relative"
    wire:ignore.self
    x-data
    @removed-post-{{ $post->id }}.window="$el.remove()">
    <x-post.owner :owner="$post->owner" />
    <div class="flex-1 flex flex-col relative">
        <x-post.header :date="$post->created_at">
            @if ($canEdit)
                <div class="flex gap-2 text-xs font-semibold">
                    @if ($editMode)
                        @if($post instanceof \App\Models\Reply)
                            <livewire:delete-post-button :post="$post" />
                        @endif
                        <button wire:click.prevent="save" class="text-green-950 hover:text-white bg-green-500 hover:bg-green-700 py-1 px-2 rounded">
                            Save
                        </button>
                    @endif
                    <button wire:click.prevent="toggleEditMode" class="text-yellow-950 hover:text-white bg-yellow-500 hover:bg-yellow-700 py-1 px-2 rounded">
                        {{ $editMode ? 'Cancel' : 'Edit' }}
                    </button>
                </div>
            @endif
        </x-post.header>
        <div class="p-4 md:p-8 flex-1">
            @if ($editMode)
                <div x-data="{
                    editor: null,
                    init() {
                        if (!this.editor) {
                            this.editor = $(`#editor-{{ $post->id }}`).trumbowyg({
                                        btns: [['viewHTML'], ['formatting'], ['strong', 'em'], ['link'], ['insertImage'],
                                            ['justifyLeft', 'justifyCenter', 'justifyRight'], ['unorderedList', 'orderedList'],
                                            ['horizontalRule'], ['removeformat']],
                                        removeformatPasted: true
                                    });
                                    this.editor.on('tbwchange', () => {
                                        @this.set('body', this.editor.trumbowyg('html'));
                                    });
                                }
                            },
                            destroy() {
                                try {
                                    if (this.editor) {
                                        this.editor.trumbowyg('destroy');
                                        this.editor = null;
                                    }
                                } catch (e) {
                                    console.error('Editor destroy error:', e);
                                }
                            }
                        }"
                     @destroy-editor.window="destroy"
                     wire:ignore>
                    <textarea id="editor-{{ $post->id }}">{{ $body }}</textarea>
                </div>
            @else
                <div class="post-body" wire:key="post-{{ $post->id }}">
                    {!! $post->body !!}
                </div>
            @endif
        </div>
        <div class="flex justify-end p-4 space-x-4">
            <livewire:quote-button :post="$post" />
            <livewire:reputation :post="$post" />
        </div>
    </div>
</article>
