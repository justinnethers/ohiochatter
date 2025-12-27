<div>
    <div class="p-2 pt-0 md:p-0">
        <x-breadcrumbs :forum="$thread->forum"/>
        @if($thread->poll)
            <livewire:poll-component :poll="$thread->poll"/>
        @endif

        @if (app('request')->input('page') == 1 || !app('request')->input('page'))
            <div class="flex gap-2">
                <div class="flex-1">
                    <livewire:post-component :post="$thread" :first-post-on-page="true"/>
                </div>
            </div>
        @endif

        @php
            $adSlots = ['2001567130', '2900286656', '2521012709', '5660018222', '7961041643'];
            $adCount = 0;
            $adFrequency = 5; // Show ad every 5 replies
        @endphp
        @foreach ($replies as $post)
            @php
                $firstPostOnPage =  false;
                if (app('request')->input('page') == 1 || !app('request')->input('page')) {
                    $firstPostOnPage = false;
                } else {
                    $firstPostOnPage = $loop->index === 0;
                }
            @endphp
            <livewire:post-component :$post :first-post-on-page="$firstPostOnPage"/>

            @guest
                @if (($loop->index + 1) % $adFrequency === 0 && !$loop->last)
                    <div class="bg-gradient-to-br from-steel-800 to-steel-850 p-4 rounded-xl mb-5 shadow-lg shadow-black/20 border border-steel-700/50">
                        <!-- In-thread Ad -->
                        <ins class="adsbygoogle"
                             style="display:block"
                             data-ad-client="ca-pub-4406607721782655"
                             data-ad-slot="{{ $adSlots[$adCount % count($adSlots)] }}"
                             data-ad-format="auto"
                             data-full-width-responsive="true"></ins>
                        <script>
                            (adsbygoogle = window.adsbygoogle || []).push({});
                        </script>
                    </div>
                    @php $adCount++ @endphp
                @endif
            @endguest
        @endforeach

        @if (auth()->check())
            <form
                class="bg-gradient-to-br from-steel-800 to-steel-850 p-4 md:p-6 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50 mb-8"
                action="{{ request()->url() }}/replies"
                method="POST"
            >
                @csrf
                <div>
                    <x-wysiwyg id="body" wire:model.defer="body" :thread="$thread"/>
                </div>
                <div class="h-4"></div>
                <div class="flex justify-between items-center">
                    <x-primary-button>
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                        Submit Post
                    </x-primary-button>
                    <livewire:thread-lock-toggle :thread="$thread"/>
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
                    editorBox.scrollIntoView({behavior: 'smooth', block: 'center'});

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
