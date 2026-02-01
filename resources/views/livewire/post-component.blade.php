<article
    id="reply-{{ $post->id }}"
    class="bg-gradient-to-br from-steel-800 to-steel-850 text-white mb-5 md:flex rounded-xl relative border border-steel-700/50 shadow-xl shadow-black/20 overflow-hidden"
    wire:ignore.self
    x-data
    @removed-post-{{ $post->id }}.window="$el.remove()">
    @if ($poll)
        <x-poll.show :$poll :$hasVoted :$voteCount/>
    @endif

    {{-- Subtle top accent line --}}
    <div
        class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-steel-600/50 to-transparent"></div>

    <x-post.owner :owner="$post->owner"/>
    <div class="flex-1 flex flex-col relative">
        <x-post.header :date="$post->created_at">
            @if ($canEdit)
                <div class="flex gap-2 text-xs font-semibold">
                    @if ($editMode)
                        @if($post instanceof \App\Models\Reply)
                            <livewire:delete-post-button :post="$post"/>
                        @endif
                        <button wire:click.prevent="save"
                                class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-lg text-white shadow-lg shadow-emerald-500/25 hover:shadow-emerald-500/40 hover:from-emerald-600 hover:to-emerald-700 transition-all duration-200">
                            Save
                        </button>
                    @endif
                    <button wire:click.prevent="toggleEditMode"
                            class="inline-flex items-center px-3 py-1.5 bg-steel-700 border border-steel-600 rounded-lg text-steel-200 hover:bg-steel-600 hover:text-white transition-all duration-200">
                        {{ $editMode ? 'Cancel' : 'Edit' }}
                    </button>
                </div>
            @endif
        </x-post.header>
        <div class="p-4 md:p-8 flex-1">
            @if ($editMode)
                <div class="relative">
                    {{-- Loading overlay --}}
                    <div wire:loading wire:target="save"
                         class="absolute inset-0 bg-steel-800 z-50 flex items-center justify-center rounded-lg">
                        <svg class="animate-spin w-8 h-8 text-accent-400" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <div class="trumbowyg-dark" x-data="{
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
                </div>
            @else
                <div class="flex gap-2">
                    <div class="post-body text-xl md:text-lg flex-1" wire:key="post-{{ $post->id }}">
                        {!! $post->formatted_body !!}
                    </div>
                    @if ($this->firstPostOnPage)
                        @guest
                            <div class="mt-4 md:mt-0 md:ml-4 w-full md:w-auto">
                                <!-- First Post Ad - Responsive -->
                                <ins class="adsbygoogle"
                                     style="display:block"
                                     data-ad-client="ca-pub-4406607721782655"
                                     data-ad-slot="6658997449"
                                     data-ad-format="auto"
                                     data-full-width-responsive="true"></ins>
                                <script>
                                    (adsbygoogle = window.adsbygoogle || []).push({});
                                </script>
                            </div>
                        @endguest
                    @endif
                </div>
            @endif
        </div>
        <div class="flex justify-end items-center p-4 space-x-4 border-t border-steel-700/30">
            <livewire:quote-button :post="$post"/>
            <livewire:reputation :post="$post"/>
        </div>
    </div>
</article>
