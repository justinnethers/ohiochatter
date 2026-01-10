<div>
    <div class="p-2 pt-0 md:p-0">
        <x-breadcrumbs :items="[
            ['title' => 'Forums', 'url' => '/forums'],
            ['title' => $thread->forum->name, 'url' => '/forums/' . $thread->forum->slug],
            ['title' => $thread->title],
        ]"/>
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
            $adFrequency = auth()->check() ? 10 : 5; // Show ad every 10 replies for auth, 5 for guests
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

            @if (($loop->index + 1) % $adFrequency === 0 && !$loop->last)
                <div
                    class="bg-gradient-to-br from-steel-800 to-steel-850 p-4 rounded-xl mb-5 shadow-lg shadow-black/20 border border-steel-700/50">
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
        @endforeach

        @auth
            @if (auth()->user()->hasVerifiedEmail())
                <form
                    class="bg-gradient-to-br from-steel-800 to-steel-850 p-4 md:p-6 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50 mb-8"
                    action="{{ request()->url() }}/replies"
                    method="POST"
                    x-data="{ submitting: false, error: '' }"
                    x-on:submit="
                        if(submitting) { $event.preventDefault(); return; }
                        const html = $('.editor').trumbowyg('html');
                        const textContent = html.replace(/<[^>]*>/g, '').trim();
                        const hasImages = /<img\s/i.test(html);
                        if(!textContent && !hasImages) {
                            $event.preventDefault();
                            error = 'Please enter a message before posting.';
                            return;
                        }
                        error = '';
                        submitting = true;
                    "
                >
                    @csrf
                    <div x-show="error" x-cloak class="mb-4 p-3 bg-red-500/20 border border-red-500/50 rounded-lg text-red-400 text-sm" x-text="error"></div>
                    @error('body')
                        <div class="mb-4 p-3 bg-red-500/20 border border-red-500/50 rounded-lg text-red-400 text-sm">
                            {{ $message }}
                        </div>
                    @enderror
                    <div>
                        <x-wysiwyg id="body" wire:model.defer="body" :thread="$thread"/>
                    </div>
                    <div class="h-4"></div>
                    <div class="flex justify-between items-center">
                        <x-primary-button x-bind:disabled="submitting" x-bind:class="{ 'opacity-50 cursor-not-allowed': submitting }">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!submitting">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                            <svg class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24" x-show="submitting" x-cloak>
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="submitting ? 'Submitting...' : 'Submit Post'"></span>
                        </x-primary-button>
                        <livewire:thread-lock-toggle :thread="$thread"/>
                    </div>
                </form>
            @else
                <div class="bg-gradient-to-br from-steel-800 to-steel-850 p-4 md:p-6 rounded-xl shadow-lg shadow-black/20 border border-steel-700/50 mb-8">
                    <div class="flex items-center gap-3 text-amber-400">
                        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <div>
                            <p class="font-semibold">Verify your email to post</p>
                            <p class="text-sm text-steel-400">Check your inbox for a verification link, or <a href="{{ route('verification.send') }}" class="text-accent-400 hover:text-accent-300 underline" onclick="event.preventDefault(); document.getElementById('resend-verification-form').submit();">click here to resend</a>.</p>
                        </div>
                    </div>
                    <form id="resend-verification-form" action="{{ route('verification.send') }}" method="POST" class="hidden">
                        @csrf
                    </form>
                </div>
            @endif
        @endauth
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
