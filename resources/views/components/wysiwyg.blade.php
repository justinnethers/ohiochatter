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

    <x-slot name="head">
        <script src="//ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script>
            window.jQuery || document.write('<script src="/js/vendor/jquery-3.3.1.min.js"><\/script>')
        </script>
        <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.30.0/dist/trumbowyg.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.30.0/dist/plugins/giphy/trumbowyg.giphy.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.30.0/dist/plugins/table/trumbowyg.table.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.30.0/dist/plugins/upload/trumbowyg.upload.min.js" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/ui/trumbowyg.min.css">
        <link rel="stylesheet"
              href="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/plugins/table/ui/trumbowyg.table.min.css">
        <link rel="stylesheet"
              href="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/plugins/giphy/ui/trumbowyg.giphy.min.css">
    </x-slot>

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
        <script>
            !function(e){"use strict";var t={enabled:!0,endpoint:"https://noembed.com/embed?nowrap=on"};e.extend(!0,e.trumbowyg,{plugins:{pasteEmbed:{init:function(n){n.o.plugins.pasteEmbed=e.extend(!0,{},t,n.o.plugins.pasteEmbed||{}),Array.isArray(n.o.plugins.pasteEmbed.endpoints)&&(n.o.plugins.pasteEmbed.endpoint=n.o.plugins.pasteEmbed.endpoints[0]),n.o.plugins.pasteEmbed.enabled&&n.pasteHandlers.push((function(t){try{var a=(t.originalEvent||t).clipboardData.getData("Text");if(!a.startsWith("http"))return;a = a.replace(/https?:\/\/(www\.)?x\.com/g, 'https://twitter.com');var s=n.o.plugins.pasteEmbed.endpoint;t.stopPropagation(),t.preventDefault();var i=new URL(s);i.searchParams.append("url",a.trim()), fetch(i,{method:"GET",cache:"no-cache",signal:AbortSignal.timeout(2e3)}).then((e=>e.json().then((e=>e.html)))).catch((()=>{})).then((t=>{void 0===t&&(t=e("<a>",{href:a,text:a})[0].outerHTML), n.execCmd("insertHTML",t)}))}catch(e){}}))}}}})}(jQuery);
        </script>
    </x-slot>
</div>
