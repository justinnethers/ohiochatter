@props(['thread'])
@php
    $userId = auth()->id();
    $threadId = $thread->id ?? null;
    $storageKey = "editor_draft_{$userId}_{$threadId}";
@endphp

<div>
    <div class="trumbowyg-dark">
        <input type="hidden" name="body" id="body-input" value="">
        <div
            class="editor text-white bg-transparent"
            style="min-height: 300px;"
            data-storage-key="{{ $storageKey }}"
            {{ $attributes }}
        ></div>
    </div>

    <x-giphy-modal />

    <x-slot name="head">
        <script src="//ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <script>
            window.jQuery || document.write('<script src="/js/vendor/jquery-3.3.1.min.js"><\/script>')
        </script>
        <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.30.0/dist/trumbowyg.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.30.0/dist/plugins/table/trumbowyg.table.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/trumbowyg@2.30.0/dist/plugins/upload/trumbowyg.upload.min.js" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/ui/trumbowyg.min.css">
        <link rel="stylesheet"
              href="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/plugins/table/ui/trumbowyg.table.min.css">
        <style>
            .trumbowyg-giphy-button {
                display: flex !important;
                align-items: center;
                justify-content: center;
                font-size: 11px !important;
                font-weight: 700 !important;
                color: #94a3b8 !important;
                text-transform: uppercase;
                width: auto !important;
                padding: 0 8px !important;
            }
            .trumbowyg-giphy-button:hover {
                color: #e2e8f0 !important;
            }
            .trumbowyg-giphy-button svg {
                display: none !important;
            }
        </style>
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
                        },
                        giphy: {
                            fn: function() {
                                window.dispatchEvent(new CustomEvent('open-giphy-modal'));
                            },
                            title: 'Insert GIF',
                            text: 'GIF',
                            hasIcon: false
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

                // Listen for GIF selection from custom modal
                window.addEventListener('giphy-selected', function(e) {
                    if (e.detail && e.detail.url) {
                        var imgHtml = '<img src="' + e.detail.url + '" alt="GIF">';
                        editor.trumbowyg('execCmd', {
                            cmd: 'insertHTML',
                            param: imgHtml,
                            forceCss: false
                        });
                    }
                });

                // Load saved content on initialization
                // const savedContent = localStorage.getItem(storageKey);
                // if (savedContent) {
                //     editor.trumbowyg('html', savedContent);
                // }

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
                    // Populate hidden input on form submit
                    form.addEventListener('submit', function(e) {
                        const content = editor.trumbowyg('html');
                        document.getElementById('body-input').value = content;
                        localStorage.removeItem(storageKey);
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
