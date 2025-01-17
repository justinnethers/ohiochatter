<div>
    <div
        class="editor text-white bg-transparent"
        style="min-height: 300px;"
        {{ $attributes }}
    ></div>

    <x-slot name="head">
{{--        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/ui/trumbowyg.min.css">--}}
{{--        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/plugins/table/ui/trumbowyg.table.min.css">--}}
{{--        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/plugins/giphy/ui/trumbowyg.giphy.min.css">--}}
    </x-slot>

    <x-slot name="footer">
        @php $key = config('services.giphy.key') @endphp
{{--        <script src="//ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>--}}
{{--        <script>window.jQuery || document.write('<script src="js/vendor/jquery-3.3.1.min.js"><\/script>')</script>--}}
{{--        <script src="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/trumbowyg.min.js"></script>--}}
{{--        <script src="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/plugins/giphy/trumbowyg.giphy.min.js"></script>--}}
{{--        <script src="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/plugins/table/trumbowyg.table.min.js"></script>--}}
{{--        <script src="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/plugins/upload/trumbowyg.upload.min.js"></script>--}}
{{--        <script src="https://cdnjs.cloudflare.com/ajax/libs/Trumbowyg/2.27.3/plugins/pasteembed/trumbowyg.pasteembed.min.js"></script>--}}
        <script>
            let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            $('.editor').trumbowyg({
                btnsDef: {
                    // Create a new dropdown
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
        </script>
    </x-slot>
</div>
