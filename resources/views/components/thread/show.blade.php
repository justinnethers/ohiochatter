<div>
    <x-breadcrumbs :forum="$thread->forum" />

    <div class="p-2 pt-0 md:p-0">
        @if($thread->poll)
            <livewire:poll-component :poll="$thread->poll" />
        @endif

        @if (app('request')->input('page') == 1 || !app('request')->input('page'))
            <div class="flex gap-2">
                <div class="flex-1">
                    <livewire:post-component :post="$thread" />
                </div>
{{--                <div class="hidden md:block md:w-1/5">--}}
{{--                    <article class="bg-gray-800 p-3 md:px-4 md:pt-4 md:pb-5 text-gray-100 font-body rounded md:rounded-md mb-2 md:mb-6 shadow-lg">--}}
{{--                        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-4406607721782655"--}}
{{--                                crossorigin="anonymous"></script>--}}
{{--                        <!-- First Post Ad Square -->--}}
{{--                        <ins class="adsbygoogle"--}}
{{--                             style="display:inline-block;width:250px;height:250px"--}}
{{--                             data-ad-client="ca-pub-4406607721782655"--}}
{{--                             data-ad-slot="6658997449"></ins>--}}
{{--                        <script>--}}
{{--                            (adsbygoogle = window.adsbygoogle || []).push({});--}}
{{--                        </script>--}}
{{--                    </article>--}}
{{--                </div>--}}
            </div>
        @endif

        @foreach ($replies as $post)
                <livewire:post-component :$post />
        @endforeach

            @if (auth()->check())
                <form
                    class="p-0 rounded-lg shadow mb-8"
                    action="{{ request()->url() }}/replies"
                    method="POST"
                >
                    @csrf
                    <div>
                        <x-wysiwyg id="body" wire:model.defer="body" :thread="$thread" />
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

            // Create temporary div to clean the quote
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = event.quote;

            // Clean nested blockquotes
            const blockquote = tempDiv.querySelector('blockquote');
            if (blockquote) {
                const nestedQuotes = blockquote.querySelectorAll('blockquote');
                nestedQuotes.forEach(quote => quote.remove());
            }

            const cleanedQuote = tempDiv.innerHTML;
            const uniqueClass = 'to-focus-' + Date.now();

            // Create the new content with proper structure
            const newContent = `${currentContent}
            ${cleanedQuote}
            <p class="${uniqueClass}"><br></p>`;

            // Set the HTML content
            body.trumbowyg('html', newContent);

            // Handle focusing
            setTimeout(() => {
                const editorBox = body.closest('.trumbowyg-box')[0];
                if (editorBox) {
                    editorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });

                    setTimeout(() => {
                        const editableDiv = editorBox.querySelector('.trumbowyg-editor');
                        if (editableDiv) {
                            const toFocus = editableDiv.querySelector(`p.${uniqueClass}`);
                            if (toFocus) {
                                // Ensure the paragraph has content for proper focus
                                if (!toFocus.firstChild) {
                                    toFocus.innerHTML = '<br>';
                                }

                                // Create and set the selection range
                                const range = document.createRange();
                                const sel = window.getSelection();

                                // Set the range to the start of the paragraph
                                range.setStart(toFocus, 0);
                                range.collapse(true);

                                // Apply the selection
                                sel.removeAllRanges();
                                sel.addRange(range);

                                // Focus the editor
                                editableDiv.focus();
                            }
                        }
                    }, 500);
                }
            }, 150);
        });
    });
</script>
