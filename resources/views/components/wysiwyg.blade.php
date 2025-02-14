@props(['thread'])
@php
    $key = config('services.giphy.key');
    $userId = auth()->id();
    $threadId = $thread->id ?? null;
    $storageKey = "editor_draft_{$userId}_{$threadId}";
@endphp

<div>
    <div class="trumbowyg-dark">
        <div
            class="editor text-white bg-transparent"
            style="min-height: 300px;"
            data-storage-key="{{ $storageKey }}"
            {{ $attributes }}
        ></div>
    </div>

    <x-slot name="footer">
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const AUTOSAVE_DELAY = 1000;
                let saveTimeout;
                let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                const editorElement = document.querySelector('.editor');
                const storageKey = editorElement.dataset.storageKey;

                // Initialize editor
                const editor = $('.editor').trumbowyg({
                    btnsDef: {
                        image: {
                            dropdown: ['insertImage', 'upload'],
                            ico: 'insertImage'
                        }
                    },
                    btns:[
                        ['strong', 'em', 'del'],
                        ['unorderedList', 'orderedList'],
                        ['image','giphy'],
                        ['blockquote'],
                        ['link'],
                        ['table'],
                        ['viewHTML'],
                        ['fullscreen']
                    ],
                    defaultLinkTarget: '_blank',
                    plugins: {
                        giphy: {
                            apiKey: '{{ $key }}'
                        },
                        upload: {
                            serverPath: '/upload-image?_token=' + csrfToken,
                            fileFieldName: 'image',
                            urlPropertyName: 'url',
                            error: (error) => {
                                console.log('error', error)
                            }
                        }
                    }
                });

                // Load saved content on initialization
                const savedContent = localStorage.getItem(storageKey);
                if (savedContent) {
                    editor.trumbowyg('html', savedContent);
                }

                // Set up auto-save functionality
                editor.on('tbwchange', function() {
                    if (saveTimeout) {
                        clearTimeout(saveTimeout);
                    }

                    saveTimeout = setTimeout(function() {
                        const content = editor.trumbowyg('html');
                        localStorage.setItem(storageKey, content);
                        console.log('Content auto-saved');
                    }, AUTOSAVE_DELAY);
                });

                // Find the form using DOM traversal
                const form = editorElement.closest('form');

                if (form) {
                    // Add a hidden input to mark form submission
                    const submitFlag = document.createElement('input');
                    submitFlag.type = 'hidden';
                    submitFlag.name = 'editor_submitted';
                    submitFlag.value = 'true';
                    form.appendChild(submitFlag);

                    // Handle form submission
                    form.addEventListener('submit', function(e) {
                        console.log('Form submission detected');
                        localStorage.removeItem(storageKey);
                        console.log('Local storage cleared for key:', storageKey);
                    });
                } else {
                    console.warn('Form element not found for editor');
                }
            });
        </script>
    </x-slot>
</div>
