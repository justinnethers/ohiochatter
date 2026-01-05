<div>
    {{-- Comment Form --}}
    @auth
        <form
            x-data="{ submitting: false }"
            x-on:submit.prevent="
                submitting = true;
                $wire.set('body', $('#comment-editor').trumbowyg('html'));
                $wire.addComment().then(() => {
                    $('#comment-editor').trumbowyg('html', '');
                    submitting = false;
                });
            "
            class="mb-6"
        >
            <div class="bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl p-4 border border-steel-700/50">
                <div wire:ignore>
                    <div class="trumbowyg-dark">
                        <div id="comment-editor" class="editor text-white bg-transparent"></div>
                    </div>
                </div>
                @error('body')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
                <div class="mt-3 flex justify-end">
                    <button
                        type="submit"
                        x-bind:disabled="submitting"
                        class="px-4 py-2 bg-gradient-to-r from-accent-500 to-accent-600 rounded-lg text-white font-semibold text-sm shadow-lg shadow-accent-500/25 hover:shadow-accent-500/40 hover:from-accent-600 hover:to-accent-700 transition-all duration-200 disabled:opacity-50"
                    >
                        <span x-show="!submitting">Post Comment</span>
                        <span x-show="submitting" x-cloak>Posting...</span>
                    </button>
                </div>
            </div>
        </form>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof $ !== 'undefined' && $('#comment-editor').length) {
                    let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    $('#comment-editor').trumbowyg({
                        btnsDef: {
                            image: {
                                dropdown: ['insertImage', 'upload'],
                                ico: 'insertImage'
                            },
                            giphy: {
                                fn: function () {
                                    window.dispatchEvent(new CustomEvent('open-giphy-modal'));
                                },
                                title: 'Insert GIF',
                                text: 'GIF',
                                hasIcon: false
                            }
                        },
                        btns: [
                            ['strong', 'em', 'del'],
                            ['unorderedList', 'orderedList'],
                            ['image', 'giphy'],
                            ['link'],
                            ['viewHTML'],
                            ['fullscreen']
                        ],
                        autogrow: true,
                        defaultLinkTarget: '_blank',
                        plugins: {
                            upload: {
                                serverPath: '/upload-image?_token=' + csrfToken,
                                fileFieldName: 'image',
                                urlPropertyName: 'url'
                            }
                        }
                    });

                    // Listen for GIF selection
                    window.addEventListener('giphy-selected', function(e) {
                        if (e.detail && e.detail.url) {
                            var imgHtml = '<img src="' + e.detail.url + '" alt="GIF">';
                            $('#comment-editor').trumbowyg('execCmd', {
                                cmd: 'insertHTML',
                                param: imgHtml,
                                forceCss: false
                            });
                        }
                    });
                }
            });
        </script>
    @else
        <div class="mb-6 p-4 bg-steel-700/50 rounded-lg text-center">
            <p class="text-steel-300">
                <a href="{{ route('login') }}" class="text-accent-400 hover:text-accent-300">Log in</a>
                to join the discussion
            </p>
        </div>
    @endauth

    {{-- Comments List --}}
    @if($comments->isEmpty())
        <div class="text-center py-8">
            <p class="text-steel-400">No comments yet. Be the first to comment!</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($comments as $comment)
                <div class="bg-gradient-to-br from-steel-800 to-steel-850 rounded-xl p-4 border border-steel-700/50">
                    <div class="flex items-start gap-3">
                        <a href="{{ route('profile.show', $comment->owner->username) }}" class="shrink-0">
                            <img src="{{ $comment->owner->avatar_path }}" alt="" class="w-10 h-10 rounded-full bg-steel-600">
                        </a>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <a href="{{ route('profile.show', $comment->owner->username) }}" class="font-semibold text-white hover:text-accent-400 transition-colors">
                                    {{ $comment->owner->username }}
                                </a>
                                <span class="text-xs text-steel-500">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="text-steel-300 prose prose-invert prose-sm max-w-none post-body">
                                {!! $comment->body !!}
                            </div>
                        </div>
                        @if(auth()->check() && (auth()->id() === $comment->user_id || auth()->user()->is_admin))
                            <button
                                wire:click="deleteComment({{ $comment->id }})"
                                wire:confirm="Are you sure you want to delete this comment?"
                                class="shrink-0 p-1.5 text-steel-500 hover:text-red-400 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $comments->links() }}
        </div>
    @endif
</div>
