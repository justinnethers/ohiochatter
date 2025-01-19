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
                    <div>
                        <x-wysiwyg id="body" wire:model.defer="body" />
                    </div>
                    <div class="h-4"></div>
                    <div class="flex justify-between">
                        <x-primary-button>Submit Post</x-primary-button>
                        <livewire:thread-lock-toggle :thread="$thread" />
                    </div>
                </form>
            @endif
    </div>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('insertQuote', (event) => {
            const body = $('#body');
            const currentContent = body.trumbowyg('html') || '';
            body.trumbowyg('html', currentContent + event.quote + "<br><p class='to-focus'></p>");

            // Wait a tiny bit for the content to be inserted
            setTimeout(() => {
                const editorBox = body.closest('.trumbowyg-box')[0];
                if (editorBox) {
                    // First scroll smoothly
                    editorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });

                    // Then wait for scroll to complete before focusing
                    setTimeout(() => {
                        const editableDiv = editorBox.querySelector('.trumbowyg-editor');
                        if (editableDiv) {
                            // Focus the editor
                            editableDiv.focus();

                            // Move cursor to the empty paragraph we added
                            const toFocus = editableDiv.querySelector('.to-focus');
                            if (toFocus) {
                                const range = document.createRange();
                                const sel = window.getSelection();
                                range.setStart(toFocus, 0);
                                range.collapse(true);
                                sel.removeAllRanges();
                                sel.addRange(range);
                            }
                        }
                    }, 500); // Wait for scroll to complete
                }
            }, 150);
        });
    });
</script>
